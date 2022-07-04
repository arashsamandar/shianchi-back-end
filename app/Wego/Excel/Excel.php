<?php
namespace App\Wego\Excel;
use PHPExcel_Cell_DataValidation;
use PHPExcel_IOFactory;
use PHPExcel_NamedRange;

/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 28/02/17
 * Time: 17:10
 */
class Excel
{
    const CONFIG_SHEET_NAME = 'config';

    protected $objPHPExcel;
    protected $inputFileName;

    function __construct($fileName)
    {
        $this->inputFileName = $fileName;

        $this->objPHPExcel = PHPExcel_IOFactory::load($this->getInputFilePath());

    }

    public function addDataValidationToCell($cellName, $formula)
    {
        $objValidation = $this->objPHPExcel->getActiveSheet()->getCell($cellName)->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(false);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setErrorTitle('Input error');
        $objValidation->setError('Value is not in list.');
        $objValidation->setPromptTitle('Pick from list');
        $objValidation->setPrompt('Please pick a value from the drop-down list.');
        $objValidation->setFormula1($formula);
    }


    public function addSheet()
    {
        $objWorkSheet = $this->objPHPExcel->createSheet(); //Setting index when creating
        $objWorkSheet->setTitle(self::CONFIG_SHEET_NAME);
        $this->objPHPExcel->setActiveSheetIndexByName(self::CONFIG_SHEET_NAME);

        $this->objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
        $this->objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
        $this->objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
        $this->objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);

        $this->objPHPExcel->getActiveSheet()->getProtection()->setPassword(env('EXCEL_PASSWORD','wegobazaar'));
    }
    public function addRangeName( $cellName, $name)
    {
        $this->objPHPExcel->addNamedRange(
            new PHPExcel_NamedRange(
                $name,
                $this->objPHPExcel->getActiveSheet(),
                $cellName
            )
        );
    }

    public function setCellValue($coordinate, $value)
    {
        $this->objPHPExcel->getActiveSheet()->setCellValue($coordinate, $value);
    }

    /**
     * @return string
     */
    private function getInputFilePath()
    {
        return public_path() . '/' . $this->inputFileName.'.xls';
    }
}