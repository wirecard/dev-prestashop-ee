<?php

function setProtectedProperty($object, $property, $value)
{
    $reflection = new ReflectionClass($object);
    $reflection_property = $reflection->getProperty($property);
    $reflection_property->setAccessible(true);
    $reflection_property->setValue($object, $value);
}
