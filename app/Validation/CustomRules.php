<?php

namespace App\Validation;

class CustomRules
{
    // Validasi agar string diawali dengan huruf kapital
    public function capitalFirst($str)
    {
        // Pastikan string tidak kosong dan huruf pertama adalah kapital
        return ctype_upper($str[0]);
    }
}
