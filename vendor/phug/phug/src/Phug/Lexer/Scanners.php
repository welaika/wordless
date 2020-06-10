<?php

namespace Phug;

use Phug\Lexer\Scanner;

class Scanners
{
    public static function getList()
    {
        return [
            //TODO: Several of these are non-standard and need to be encapsulated into extensions
            //Namely: ForScanner, DoScanner, VariableScanner
            'new_line'    => Scanner\NewLineScanner::class,
            'indent'      => Scanner\IndentationScanner::class,
            'import'      => Scanner\ImportScanner::class,
            'block'       => Scanner\BlockScanner::class,
            'yield'       => Scanner\YieldScanner::class,
            'conditional' => Scanner\ConditionalScanner::class,
            'each'        => Scanner\EachScanner::class,
            'case'        => Scanner\CaseScanner::class,
            'when'        => Scanner\WhenScanner::class,
            'do'          => Scanner\DoScanner::class,
            'while'       => Scanner\WhileScanner::class,
            'for'         => Scanner\ForScanner::class,
            'mixin'       => Scanner\MixinScanner::class,
            'mixin_call'  => Scanner\MixinCallScanner::class,
            'doctype'     => Scanner\DoctypeScanner::class,
            'keyword'     => Scanner\KeywordScanner::class,
            'tag'         => Scanner\TagScanner::class,
            'class'       => Scanner\ClassScanner::class,
            'id'          => Scanner\IdScanner::class,
            'attribute'   => Scanner\AttributeScanner::class,
            'assignment'  => Scanner\AssignmentScanner::class,
            'variable'    => Scanner\VariableScanner::class,
            'comment'     => Scanner\CommentScanner::class,
            'filter'      => Scanner\FilterScanner::class,
            'expression'  => Scanner\ExpressionScanner::class,
            'code'        => Scanner\CodeScanner::class,
            'markup'      => Scanner\MarkupScanner::class,
            'expansion'   => Scanner\ExpansionScanner::class,
            'dynamic_tag' => Scanner\DynamicTagScanner::class,
            'text_block'  => Scanner\TextBlockScanner::class,
            'text_line'   => Scanner\TextLineScanner::class,
            //Notice that TextScanner is always added in lex(), as we'd basically disable extensions otherwise
            //As this array is replaced recursively, your extensions are either added or overwritten
            //If Text would be last one, every extension would end up as text, as text matches everything
        ];
    }
}
