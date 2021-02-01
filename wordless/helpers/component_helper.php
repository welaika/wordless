<?php

namespace Wordless;

use \Symfony\Component\Validator\Validation;
use \Symfony\Component\Validator\Mapping\ClassMetadata;
use \Symfony\Component\Validator\Constraints as Assert;

class ComponentValidationException extends \Exception {}

// Implementation example

// use Symfony\Component\Validator\Constraints as Assert;
// use Symfony\Component\Validator\Context\ExecutionContextInterface;

// class MyComponent extends \Wordless\Component {
//     public $post;

//     public static function loadValidatorMetadata($metadata)
//     {
//         $metadata->addPropertyConstraint('post', new Assert\Type(WP_Post::class));
//         $metadata->addConstraint(new Assert\Callback('validate_post_type'));
//     }

//     public function validate_post_type(ExecutionContextInterface $context, $payload)
//     {
//         if( !in_array($this->post->post_type, ['post']) ) {
//             $context->buildViolation('WP_Post passed to this component must be of type "post"')
//                 ->atPath('post')
//                 ->addViolation();
//         }
//     }
// }
//
// Then in PUG
//
// mixin MyComponent($component)
//   h1= $component->post->post_title
//
// - $component = new MyComponent(['post' => get_post(1)])
// +MyComponent($component)
//
// More docs about Symphony\Component\Validator @ https://symfony.com/doc/current/validation.html#constraints
abstract class Component {

    protected $locals = [];
    private $validator = null;

    function __construct(array $locals) {
        $this->validator = Validation::createValidatorBuilder()->addMethodMapping('loadValidatorMetadata')->getValidator();
        $this->locals = $locals;
        try {
            $this->setProperties();
            $this->validate();
        } catch (ComponentValidationException $e) {
            if ( 'production' === ENVIRONMENT ) {
                do_action('wordless_component_validation_exception', $e);
                // Would be nice to have an exception collector in your callback, e.g. Sentry:
                //
                // function yourhandler(\Wordless\ComponentValidationException $e) {
                //      if ( function_exists( 'wp_sentry_safe' ) ) {
                //          wp_sentry_safe( function ( \Sentry\State\HubInterface $client ) use ( $e ) {
                //                         $client->captureException( $e );
                //                     } );
                //      }
                // }
                // add_action('wordless_component_validation_exception', 'yourhandler', 10, 1)
            } else {
                render_error('Component validation error', $e->getMessage());
            }
        }
    }

    abstract static function loadValidatorMetadata(ClassMetadata $metadata);

    protected function validate() {
        $violations = $this->validator->validate($this);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                throw new ComponentValidationException(get_class($violation->getRoot()).'.'.$violation->getPropertyPath().': ' . $violation->getMessage(), 1);
            }
        }

        return true;
    }

    protected function setProperties() {
        foreach ($this->locals as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            } else {
                throw new ComponentValidationException("You're trying to set the $key component attribute, but you have not declared the property inside component's class " . get_class($this), 1);
            }
        }
    }
}
