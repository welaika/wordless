<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Compiler\Layout;
use Phug\CompilerException;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\MixinElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\Node\YieldNode;
use Phug\Parser\NodeInterface;

class ImportNodeCompiler extends AbstractNodeCompiler
{
    protected function isPugImport($path)
    {
        $compiler = $this->getCompiler();
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: '';
        $extensions = $compiler->getOption('extensions');

        if ($extension === '') {
            return in_array('', $extensions);
        }

        if (!$compiler->getOption('allow_composite_extensions')) {
            return in_array(".$extension", $extensions, true);
        }

        foreach ($extensions as $endPattern) {
            if (substr($path, -strlen($endPattern)) === $endPattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param NodeInterface    $node
     * @param ElementInterface $parent
     *
     * @throws CompilerException
     *
     * @return null|ElementInterface
     */
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof ImportNode,
            'Unexpected '.get_class($node).' given to import compiler.',
            $node
        );

        /** @var ImportNode $node */
        $path = $node->getPath();
        $isAbsolutePath = mb_substr($path, 0, 1) === '/';

        $compiler->assert(
            !($isAbsolutePath && empty($compiler->getOption('paths'))),
            'Either the "basedir" or "paths" option is required'.
            ' to use includes and extends with "absolute" paths'
        );

        $paths = $isAbsolutePath
            ? null
            : [dirname($compiler->getPath()) ?: '.'];

        $path = $compiler->resolve($node->getPath(), $paths);
        $compiler->registerImportPath($path);

        /** @var FilterNode $filter */
        if ($filter = $node->getFilter()) {
            $text = new TextNode();
            $text->setValue($compiler->getFileContents($path));
            $filter->appendChild($text);
            $import = $filter->getImport();
            $filter->setImport(null);
            $element = $compiler->compileNode($filter, $parent);
            $filter->setImport($import);

            return $element;
        }

        if (!$this->isPugImport($path)) {
            return new TextElement($compiler->getFileContents($path), $node);
        }

        $subCompiler = clone $compiler;
        $subCompiler->setParentCompiler($compiler);
        $subCompiler->setImportNode($compiler->getImportNode() ?: $node);
        $element = $subCompiler->compileFileIntoElement($path);
        $compiler->importBlocks($subCompiler->getBlocks());
        $isIncludeImport = $node->getName() === 'include';

        if ($layout = $subCompiler->getLayout()) {
            $element = $layout->getDocument();
            $layoutCompiler = $layout->getCompiler();
            if ($isIncludeImport) {
                $layoutCompiler->compileBlocks();
            }
        }

        if (!$subCompiler->isImportNodeYielded()) {
            $yield = $element;
            while ($yield instanceof ElementInterface && $yield->hasChildren()) {
                $yield = $yield->getChildAt($yield->getChildCount() - 1);
            }
            while ($yield instanceof CodeElement ||
                $yield instanceof ExpressionElement ||
                $yield instanceof TextElement
            ) {
                if ($yield instanceof CodeElement &&
                    trim($yield->getValue(), '; ') === 'break' &&
                    $yield->getPreviousSibling()
                ) {
                    $yield = $yield->getPreviousSibling();

                    continue;
                }
                $yield = $yield->getParent();
            }
            if ($yield instanceof MarkupElement && in_array($yield->getName(), [
                'script',
                'link',
                'img',
                'input',
                'meta',
                'hr',
            ])) {
                $yield = $yield->getParent();
            }
            if ($compiler->getImportNode()) {
                $node->appendChild(new YieldNode());
            }
            // TODO: check answers on https://github.com/pugjs/pug/issues/2878
            $this->compileNodeChildren($node, $yield);
        }

        if ($isIncludeImport) {
            $parentLayout = $compiler->getLayout();
            if ($parentLayout) {
                $parentDocument = $parentLayout->getDocument();
                foreach ($element->getChildren() as $child) {
                    if ($child instanceof MixinElement) {
                        $parentDocument->appendChild(clone $child);
                    }
                }
            }

            return $element;
        }

        if ($node->getName() === 'extend') {
            $layout = new Layout($element, $subCompiler);
            $subDocument = $layout->getDocument();
            foreach ($node->getParent()->getChildren() as $child) {
                if ($child instanceof MixinNode) {
                    $mixinElement = $subCompiler->compileNode($child, $subDocument);
                    if ($mixinElement) {
                        $subDocument->appendChild($mixinElement);
                    }
                }
            }
            $compiler->setLayout($layout);
        }

        return null;
    }
}
