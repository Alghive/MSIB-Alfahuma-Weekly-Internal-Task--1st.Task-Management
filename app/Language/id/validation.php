<?php

return [
    // Pesan validasi umum
    'required'      => 'Field {field} diperlukan.',
    'min_length'    => '{field} harus memiliki minimal {param} karakter.',
    'max_length'    => '{field} tidak boleh melebihi {param} karakter.',
    'is_unique'     => '{field} sudah digunakan, pilih yang lain.',
    'valid_email'   => 'Email yang dimasukkan harus valid.',
    'matches'       => '{field} harus sesuai dengan {param}.',
    'in_list'       => '{field} harus salah satu dari: {param}.',

    // Pesan khusus untuk task
    'title'         => 'Judul harus memiliki minimal 3 karakter.',
    'description'   => 'Deskripsi task diperlukan.',
    'status'        => 'Status hanya boleh diisi dengan salah satu dari: done, on progress, pending.',
];
