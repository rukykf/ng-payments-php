<?php


namespace Metav\NgPayments\Interfaces;

interface ApiDataMapperInterface
{
    public function save();

    public static function create();

    public static function list();

    public static function fetch($id);

    public static function update($id, $attributes);
}
