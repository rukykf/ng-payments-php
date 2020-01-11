<?php


namespace Kofi\NgPayments\Interfaces;

interface ApiDataMapperInterface
{
    public function save();

    public static function fetchAll($query_params);

    public static function fetch($id);

    public static function delete($id);
}
