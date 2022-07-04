<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Maatwebsite\Excel\Files\ExcelFile;

class excelRequest extends ExcelFile
{
    /**
     * Get file
     * @return string
     */
    public function getFile()
    {
        return public_path().'/tempq.xls';
    }
}
