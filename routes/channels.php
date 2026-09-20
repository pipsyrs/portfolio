<?php

use Illuminate\Support\Facades\Broadcast;

/*
 * Channel notifikasi pribadi milik pemilik dashboard.
 *
 * Perbandingan dilakukan sebagai string: id pengguna berupa UUID, dan casting
 * ke int membuat keduanya bernilai 0 sehingga siapa pun akan lolos otorisasi.
 */
Broadcast::channel('App.Models.User.{id}', function ($user, string $id) {
    return hash_equals((string) $user->getKey(), $id);
});
