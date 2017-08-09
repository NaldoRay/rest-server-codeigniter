<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

class Validation_test extends TestCase
{
    /** @var Rest_validation */
    private $validation;

    public function __construct ()
    {
        // autoload exceptions
        spl_autoload_register(function ($class)
        {
            $filepath = __DIR__ . '/../../exceptions/' . $class . '.php';
            if (file_exists($filepath))
                include_once($filepath);
        });
    }

    protected function setUp ()
    {
        $this->validation = $this->newLibrary('rest_validation');

        $data = [
            'name' => 'Ray',
            'username' => 'raynaldo@unpar.ac.id',
            'email' => 'wrong-email@format',
            'address' => '',
        ];
        $this->validation->forArray($data);
    }

    public function testRequiredShouldSuccess ()
    {
        $this->validation->field('name')->required();
        $this->validation->field('username')->required();
        $this->validation->validate();
    }

    public function testRequiredShouldFailed ()
    {
        $this->setExpectedException(ValidationException::class);

        $data = [
            'address' => ' '
        ];
        $this->validation->forArray($data);
        $this->validation->field('address')->required();
        $this->validation->field('password')->required();
        $this->validation->validate();
    }

    public function testEmailShouldValid ()
    {
        $this->validation->field('username')->validEmail();
        $this->validation->validate();
    }

    public function testEmailShouldInvalid ()
    {
        $this->setExpectedException(ValidationException::class);

        $this->validation->field('email')->validEmail();
        $this->validation->validate();
    }

    public function testMinLengthShouldSuccess ()
    {
        $this->validation->field('username')->lengthMin(4);
        $this->validation->field('name')->lengthMin(3);

        $this->validation->validate();
    }

    public function testMinLengthShouldFailed()
    {
        $this->setExpectedException(ValidationException::class);

        $this->validation->field('address')->lengthMin(1);
        $this->validation->field('name')->lengthMin(10);
        $this->validation->validate();
    }

    public function testMaxLengthShouldSuccess ()
    {
        $this->validation->field('username')->lengthMax(30);
        $this->validation->field('name')->lengthMax(3);
        $this->validation->field('address')->lengthMax(3);

        $this->validation->validate();
    }

    public function testMaxLengthShouldFailed ()
    {
        $this->setExpectedException(ValidationException::class);

        $data = [
            'name' => 'Super long name of a person'
        ];
        $this->validation->forArray($data);
        $this->validation->field('name')->lengthMax(10);
        $this->validation->validate();
    }

    public function testLengthBetweenShouldSuccess ()
    {
        $this->validation->field('username')->lengthBetween(6, 50);
        $this->validation->field('name')->lengthBetween(3, 30);

        $this->validation->validate();
    }

    public function testLengthBetweenShouldFailed ()
    {
        $this->setExpectedException(ValidationException::class);

        $this->validation->field('address')->lengthBetween(1, 50);
        $this->validation->field('username')->lengthBetween(6, 10);
        $this->validation->validate();
    }

    public function testFailedValidation ()
    {
        $this->validation->field('username')->required()->validEmail();
        $this->validation->field('name')->required()->lengthMin(3);
        $this->validation->field('email')->required()->validEmail();
        $this->validation->field('address')->required();
        $this->validation->field('password')->required();
        try
        {
            $this->validation->validate();
            $this->assertTrue(false);
        }
        catch (ValidationException $e)
        {
            $errors = $e->getErrors();
            $this->assertTrue(!isset($errors['username']));
            $this->assertTrue(!isset($errors['name']));

            $this->assertTrue(!empty($errors['email']));
            $this->assertTrue(!empty($errors['address']));
            $this->assertTrue(!empty($errors['address']));
            $this->assertTrue(!empty($errors['password']));
        }
    }
}
