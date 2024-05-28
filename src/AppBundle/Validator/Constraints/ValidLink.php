<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 1/21/16
 * Time: 7:19 PM
 */
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ValidLink
 * @package AppBundle\Validator\Constraints
 *
 * @Annotation
 */
class ValidLink extends Constraint
{
    public $message = '"%link%" link is inaccessible';
}
