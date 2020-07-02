<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\ForNode;
use Phug\Parser\NodeInterface;

class ForNodeCompiler extends EachNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof ForNode,
            'Unexpected '.get_class($node).' given to for compiler.',
            $node
        );

        /**
         * @var ForNode $node
         */
        $subject = $node->getSubject();
        if (preg_match('/^
                \s*\$?(?P<item>[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*)
                (?:
                    \s*,\s*
                    \$?(?P<key>[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*)
                )?
                \s*(?P<operator>(?:of|in))
                (?P<subject>.*)
            $/ix', $subject, $matches)
        ) {
            $key = empty($matches['key']) ? null : $matches['key'];
            $item = $matches['item'];
            $subject = trim($matches['subject']);
            if (strtolower($matches['operator']) === 'of') {
                $swap = $item;
                $item = $key;
                if ($item === null) {
                    $item = '__none';
                }
                $key = $swap;
            }

            return $this->compileLoop($node, $subject, $key, $item);
        }

        return $this->wrapStatement($node, 'for', $subject);
    }
}
