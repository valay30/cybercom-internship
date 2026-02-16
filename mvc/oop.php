<?php
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
            $this->a = new A;
        }
        print_r($this->a);
        return $this->a;

    }

}

$b = new B;
$b->a()->i(20);
echo $b->a()->g();


?>