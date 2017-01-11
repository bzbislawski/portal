<?php
namespace Portal;

use Symfony\Component\Console\Helper\Table;

class ArrayFormatter
{
    /**
     * @var array
     */
    public $workSheetArray;

    /**
     * Create workSheetArray from given filename.
     *
     * @param $fileName
     */
    public function readFile($fileName)
    {
        $file = fopen($fileName,"r");
        $workSheetArray = [];
        while(! feof($file)) {
            array_push($workSheetArray, fgetcsv($file));
        }

        fclose($file);

        $this->workSheetArray = $workSheetArray;
    }

    /**
     *  Sum up all duplicates, delete duplicates.
     */
    public function sumDuplicatedEntries()
    {
        $arraySize = count($this->workSheetArray);
        for ($i = 0; $i < $arraySize; $i += 1) {
            for ($j = $i + 1; $j < $arraySize; $j += 1) {
                if ($this->workSheetArray[$i][0] == $this->workSheetArray[$j][0]) {
                    $this->workSheetArray[$j][1] += $this->workSheetArray[$i][1];
                    unset($this->workSheetArray[$i]);
                    break;
                }
            }
        }
    }

    /**
     * Delete first row to prevent from future operations on array.
     */
    public function deleteHeader()
    {
        unset($this->workSheetArray[0]);
    }

    /**
     * ReIndex array so there are no empty keys.
     */
    public function reindexArray()
    {
        $this->workSheetArray = array_values($this->workSheetArray);
    }

    /**
     * Add for each array two keys, one for bay name and one for shelf number.
     */
    public function explodeLocationDetails()
    {
        foreach ($this->workSheetArray as $key => $value) {
            $location = explode(" ", $value[2]);
            array_push($this->workSheetArray[$key], $location[0], $location[1]);
        }
    }

    /**
     * usort() array, sort from A to ZY.
     */
    public function sortArray()
    {
        // Define order
        $columns = [];
        for($i = 'A'; $i < 'ZZ'; $i++) {
            $columns[] = $i;
        }
        // Filter
        usort($this->workSheetArray, function ($firstItem, $secondItem) use ($columns) {
            if ($firstItem[3] == $secondItem[3]) {
                return $firstItem[4] < $secondItem[4] ? -1 : 1;
            }
            $firstItemKey = array_search($firstItem[3], $columns);
            $secondItemKey = array_search($secondItem[3], $columns);
            return ($firstItemKey < $secondItemKey) ? -1 : 1;
        });
    }

    /**
     * Remove unnecessary attributes of array.
     */
    public function removeLocationDetails()
    {
        $this->workSheetArray = array_map(function($array){
            return [
                0 => $array[0],
                1 => $array[1],
                2 => $array[2]
            ];
        }, $this->workSheetArray);
    }

    /**
     * Save formatted array to file output.csv.
     */
    public function dumpOutput()
    {
        $fp = fopen('output.csv', 'w');

        // Add some headers
        fputcsv($fp, ['product_code', 'quantity', 'pick_location']);

        foreach ($this->workSheetArray as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

    /**
     * Display table in the console.
     *
     * @param $output
     */
    public function showArray($output)
    {
        $table = new Table($output);
        $table->setHeaders(['product_code', 'quantity', 'pick_location'])
            ->setRows($this->workSheetArray)
            ->render();
    }
}
