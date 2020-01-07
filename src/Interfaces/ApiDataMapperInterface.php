<?php


namespace Metav\NgPayments\Interfaces;

interface ApiDataMapperInterface
{
    public function save();

    public static function list();

    public static function fetch($id);

}
