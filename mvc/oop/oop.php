<!-- <?php
echo "<pre>";
class A
{
    protected $n = 10;
    public function i($n)
    {
        $this->n = $n;
    }

    public function g()
    {
        return $this->n;
    }
}
class B
{
    public $a = null;
    public function a()
    {
        if ($this->a == null) {
            $this->a = new A();
        }
        print_r($this->a);
        return $this->a;
    }
}

$b = new B();
$b->a()->i(20);
echo $b->a()->g();

?> -->

<?php
echo "<pre>";

class Car
{
    protected $speed = 50;

    public function setSpeed($speed)
    {
        $this->speed = $speed;
    }

    public function getSpeed()
    {
        return $this->speed;
    }
}

class Garage
{
    public $car = null;

    public function getCar()
    {
        if ($this->car == null) {
            $this->car = new Car();
        }

        print_r($this->car);
        return $this->car;
    }
}

$garage = new Garage();

$garage->getCar()->setSpeed(120);

echo "Car Speed: " . $garage->getCar()->getSpeed();

?>