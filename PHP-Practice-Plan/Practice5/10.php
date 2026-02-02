<?php

class User
{
    private $name;
    private $email;
    private $data = []; // Array to store dynamic properties

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    // Magic method __set to set undefined properties
    public function __set($property, $value)
    {
        // We can store undefined properties in an array
        $this->data[$property] = $value;
    }

    // Magic method __get to handle access to undefined properties
    public function __get($property)
    {
        // Check if the property exists in our dynamic array
        if (array_key_exists($property, $this->data)) {
            return $this->data[$property];
        }

        // Return a message if it doesn't exist
        return "The property '$property' does not exist.";
    }

    // Magic method __toString to return a JSON representation of the object
    public function __toString()
    {
        // Create an array including private and dynamic properties
        $output = [
            'name' => $this->name,
            'email' => $this->email,
            'dynamic_properties' => $this->data
        ];
        return json_encode($output, JSON_PRETTY_PRINT);
    }
}

// Create a new User object
$user = new User("Valay", "valay@example.com");

// 1. Test __toString()
echo "<h3>1. Testing __toString() (JSON Output)</h3>";
echo "User Object: <pre>" . $user . "</pre>";

// 2. Test __get() with an undefined property
echo "<h3>2. Testing __get() (Undefined Property)</h3>";
echo "Accessing 'address': " . $user->address . "<br>";

// 3. Test __set() (Bonus: Setting a new property)
echo "<h3>3. Testing __set() (Setting 'address')</h3>";
$user->address = "Ahmedabad, India";
echo "Address has been set.<br>";

// 4. Test __get() again after setting
echo "Accessing 'address' now: " . $user->address . "<br>";

// 5. Show object again to see the new property in JSON
echo "<h3>Final Object State</h3>";
echo "<pre>" . $user . "</pre>";

?>
