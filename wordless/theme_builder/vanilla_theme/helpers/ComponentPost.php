<?php

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ComponentPost extends \Wordless\Component {
    public $post;

    public static function loadValidatorMetadata($metadata)
    {
        $metadata->addPropertyConstraint('post', new Assert\Type(WP_Post::class));
        $metadata->addConstraint(new Assert\Callback('validate_post_type'));
    }

    public function validate_post_type(ExecutionContextInterface $context, $payload)
    {
        if( !in_array($this->post->post_type, ['post']) ) {
            $context->buildViolation('WP_Post passed to this component must be of type "post"')
                ->atPath('post')
                ->addViolation();
        }
    }
}
