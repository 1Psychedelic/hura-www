<?php

Nette\Forms\Container::extensionMethod('addDateTimePicker', function (Nette\Forms\Container $container, $name, $label = null) {
    $control = new \Hafo\NetteBridge\Forms\Controls\DateTimeInput($label);
    $proto = $control->getControlPrototype();
    $proto->addClass('datetime-picker');

    return $container[$name] = $control;
});
Nette\Forms\Container::extensionMethod('addDate', function (Nette\Forms\Container $container, $name, $label = null) {
    $control = new \Nette\Forms\Controls\TextInput($label);
    $proto = $control->getControlPrototype();
    $proto->setAttribute('type', 'date');

    return $container[$name] = $control;
});
Nette\Forms\Container::extensionMethod('addButtonSubmit', function (Nette\Forms\Container $container, $name, $label) {
    $control = new Nette\Forms\Controls\SubmitButton($label);
    $proto = $control->getControlPrototype();
    $proto->setName('button');
    $proto->setType('submit');
    $proto->setHtml($label);

    return $container[$name] = $control;
});
Nette\Forms\Container::extensionMethod('addStarRating', function (\Nette\Forms\Container $container, $name, $label) {
    $control = new \Nette\Forms\Controls\TextInput($label);
    $proto = $control->getControlPrototype();
    $proto->addClass('rating');
    $proto->setType('number');
    $proto->data('show-clear', false);
    $proto->data('size', 'xs');
    $proto->data('show-caption', false);
    $proto->setAttribute('min', 0);
    $proto->setAttribute('max', 5);
    $proto->setAttribute('step', 1);

    return $container[$name] = $control;
});
Nette\Forms\Container::extensionMethod('addXSelect', function (Nette\Forms\Container $container, $name, $label, $items) {
    $control = $container->addSelect($name, $label, $items);
    $control->getControlPrototype()->addClass('select2');

    return $control;
});
Nette\Forms\Container::extensionMethod('addXMultiSelect', function (\Nette\Forms\Container $container, $name, $label, $items, $prompt = null, $tags = true, $checkAllowedValues = false) {
    $control = $container->addMultiSelect($name, $label, $items);
    $control->checkAllowedValues = $checkAllowedValues;
    $control->getControlPrototype()->addClass('select2');
    $control->getControlPrototype()->data('placeholder', $prompt);
    $control->getControlPrototype()->data('tags', $tags);

    return $control;
});
Nette\Forms\Container::extensionMethod('addTags', function (\Nette\Forms\Container $container, $name, $label, $items, $prompt = null, $tags = true, $checkAllowedValues = false) {
    $control = $container->addMultiSelect($name, $label, $items);
    $control->checkAllowedValues = $checkAllowedValues;
    $control->getControlPrototype()->addClass('select2');
    $control->getControlPrototype()->data('placeholder', $prompt);
    $control->getControlPrototype()->data('tags', $tags);

    return $control;
});
Nette\Forms\Container::extensionMethod('addCKEditor', function (\Nette\Forms\Container $container, $name, $label = null) {
    $control = $container->addTextArea($name, $label);
    $control->getControlPrototype()->addClass('editor');

    return $control;
});
