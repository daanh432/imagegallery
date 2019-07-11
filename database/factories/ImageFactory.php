<?php

/* @var $factory Factory */

use App\Images;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Images::class, function (Faker $faker) {
    $path = storage_path('app/public/images/1/');
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    return [
        'name' => $faker->name,
        'description' => $faker->text,
        'user_id' => 1,
        'url' => '/images/1/' . $faker->image($path, 600, 720, 'cats', false)
    ];
});
