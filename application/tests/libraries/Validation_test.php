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
            'age' => 42,
            'address' => 'Bandung',
        ];
        $this->validation->forArray($data);
    }

    public function testValueValidation ()
    {
        $this->setExpectedException(BadValueException::class);

        $this->validation->forValue('pwd', 'Password')
            ->required()
            ->lengthBetween(8, 16);

        $this->validation->validate();
    }

    public function testRequiredShouldSuccess ()
    {
        $this->validation->field('name')->required();
        $this->validation->field('username')->required();
        $this->validation->validate();
    }

    public function testRequiredShouldFailed ()
    {
        $this->setExpectedException(BadArrayException::class);

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
        $this->setExpectedException(BadArrayException::class);

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
        $this->setExpectedException(BadArrayException::class);

        $data = [
            'address' => '',
            'name' => 'abc'
        ];
        $this->validation->forArray($data);
        $this->validation->field('address')->lengthMin(1);
        $this->validation->field('name')->lengthMin(10);
        $this->validation->validate();
    }

    public function testMaxLengthShouldSuccess ()
    {
        $this->validation->field('username')->lengthMax(30);
        $this->validation->field('name')->lengthMax(3);
        $this->validation->field('age')->lengthMax(3);

        $this->validation->validate();
    }

    public function testMaxLengthShouldFailed ()
    {
        $this->setExpectedException(BadArrayException::class);

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
        $this->setExpectedException(BadArrayException::class);

        $this->validation->field('address')->lengthBetween(10, 50);
        $this->validation->field('username')->lengthBetween(6, 10);
        $this->validation->validate();
    }

    public function testNumericOnlyShouldSuccess ()
    {
        $data = [
            'age' => 42,
            'id' => '01'
        ];
        $this->validation->forArray($data);
        $this->validation->field('age')->onlyNumeric();
        $this->validation->field('id')->onlyNumeric();
        $this->validation->validate();
    }

    public function testNumericOnlyShouldFailed ()
    {
        $this->setExpectedException(BadArrayException::class);

        $data = [
            'id' => '01 '
        ];
        $this->validation->forArray($data);
        $this->validation->field('id')->onlyNumeric();
        $this->validation->validate();
    }

    public function testRepeatedValidationShouldOverride ()
    {
        $this->validation->field('username')->validEmail()->lengthMax(6)->required()->lengthMax(30);
        $this->validation->validate();
    }

    public function testValidationOrderShouldCorrect ()
    {
        $this->validation->field('address')->lengthMax(6)->validEmail()->required();
        $this->validation->field('password')->onlyNumeric()->lengthMin(6)->required();
        try
        {
            $this->validation->validate();
            $this->assertTrue(false);
        }
        catch (BadArrayException $e)
        {
            $errors = $e->getAllErrors();
            $this->assertContains('valid email', $errors['address']);
            $this->assertContains('required', $errors['password']);
        }
    }

    public function testCustomValidationShouldBeExecuted ()
    {
        $this->validation->field('username')
            ->required()
            ->addValidation(function ($value)
            {
                return false;
            }, "Custom validation failed");

        $this->validation->field('address')
            ->required()
            ->addValidation(function ($value)
            {
                return false;
            }, "Custom validation failed")
            ->lengthMin(10);

        try
        {
            $this->validation->validate();
            $this->assertTrue(false);
        }
        catch (BadArrayException $e)
        {
            $errors = $e->getAllErrors();
            $this->assertContains('Custom validation', $errors['username']);
            $this->assertContains('at least', $errors['address']);
        }
    }

    public function testFailedValidation ()
    {
        $this->validation->field('username')->required()->validEmail();
        $this->validation->field('name')->required()->lengthMin(3);
        $this->validation->field('email')->required()->validEmail();
        $this->validation->field('address')->required()->lengthMin(10);
        $this->validation->field('password')->required();
        $this->validation->field('age')->onlyNumeric();

        try
        {
            $this->validation->validate();
            $this->assertTrue(false);
        }
        catch (BadArrayException $e)
        {
            $errors = $e->getAllErrors();
            $this->assertTrue(!isset($errors['username']));
            $this->assertTrue(!isset($errors['name']));
            $this->assertTrue(!isset($errors['age']));

            $this->assertTrue(!empty($errors['email']));
            $this->assertTrue(!empty($errors['address']));
            $this->assertTrue(!empty($errors['address']));
            $this->assertTrue(!empty($errors['password']));
        }
    }
}