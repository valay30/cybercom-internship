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

// Method chaining (same style)
$garage->getCar()->setSpeed(120);

// Again calling same object
echo "Car Speed: " . $garage->getCar()->getSpeed();

?>