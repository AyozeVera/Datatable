<?php namespace Chumper\Datatable\Engines;

use Illuminate\Support\Collection;

/**
 * This handles the collections,
 * it needs to compile first, so we wait for the make command and then
 * do all the operations
 *
 * Class CollectionEngine
 * @package Chumper\Datatable\Engines
 */
class CollectionEngine extends BaseEngine {

    /**
     * @var \Illuminate\Support\Collection
     */
    private $workingCollection;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $collection;

    /**
     * @var array Different options
     */
    private $options = array(
        'stripOrder'        =>  false,
        'stripSearch'       =>  false,
        'caseSensitive'     =>  false,
    );

    /**
     * @param Collection $collection
     */
    function __construct(Collection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
        $this->workingCollection = $collection;
    }

    /**
     * @param $column
     * @param $order
     */
    public function order($column, $order = BaseEngine::ORDER_ASC)
    {
        $this->orderColumn = $column;
        $this->orderDirection = $order;
    }

    /**
     * @param $value
     */
    public function search($value)
    {
        $this->search = $value;
    }

    /**
    * @param string $columnName
    * @param mixed $value
    */
    public function searchOnColumn($columnName, $value)
    {
        // is not yet implemented in this engine
    }

    /**
     * @param $value
     */
    public function skip($value)
    {
        $this->skip = $value;
    }

    /**
     * @param $value
     */
    public function take($value)
    {
        $this->limit = $value;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->workingCollection->count();
    }

    /**
     * @return int
     */
    public function totalCount()
    {
        return $this->collection->count();
    }

    /**
     * @return array
     */
    public function getArray()
    {
        $this->doInternalSearch(new Collection(), array());
        $this->doInternalOrder();

        return array_values($this->workingCollection
            ->slice($this->skip,$this->limit)
            ->toArray()
        );
    }

    /**
     * Resets all operations performed on the collection
     */
    public function reset()
    {
        $this->workingCollection = $this->collection;
    }

    public function stripSearch()
    {
        $this->options['stripSearch'] = true;
        return $this;
    }

    public function stripOrder()
    {
        $this->options['stripOrder'] = true;
        return $this;
    }

    //--------------PRIVATE FUNCTIONS-----------------

    protected function internalMake(Collection $columns, array $searchColumns = array())
    {
        $this->compileArray($columns);
        $this->doInternalSearch($columns, $searchColumns);
        $this->doInternalOrder();

        return $this->workingCollection->slice($this->skip,$this->limit);
    }

    private function doInternalSearch(Collection $columns, array $searchColumns)
    {
        if(is_null($this->search) or empty($this->search))
            return;

        $value = $this->search;
        $caseSensitive = $this->options['caseSensitive'];

        $toSearch = array();

        // Map the searchColumns to the real columns
        $ii = 0;
        foreach($columns as $i => $col)
        {
            if(in_array($columns->get($i)->getName(), $searchColumns))
            {
                $toSearch[] = $ii;
            }
            $ii++;
        }

        $this->workingCollection = $this->workingCollection->filter(function($row) use ($value, $toSearch, $caseSensitive)
        {
            for($i = 0; $i < count($row); $i++)
            {
                if(!in_array($i, $toSearch))
                    continue;

                if($this->options['stripSearch'])
                {
                    $search = strip_tags($row[$i]);
                }
                else
                {
                    $search = $row[$i];
                }
                if($caseSensitive)
                {
                    if(str_contains($search,$value))
                        return true;
                }
                else
                {
                    if(str_contains(strtolower($search),strtolower($value)))
                        return true;
                }
            }
        });
    }

    private function doInternalOrder()
    {
        if(is_null($this->orderColumn))
            return;

        $column = $this->orderColumn;
        $stripOrder = $this->options['stripOrder'];
        $this->workingCollection->sortBy(function($row) use ($column,$stripOrder) {

            if($stripOrder)
            {
                return strip_tags($row[$column]);
            }
            else
            {
                return $row[$column];
            }
        });

        if($this->orderDirection == BaseEngine::ORDER_DESC)
            $this->workingCollection = $this->workingCollection->reverse();
    }

    private function compileArray($columns)
    {
        $this->workingCollection = $this->collection->map(function($row) use ($columns) {
            $entry = array();
            foreach ($columns as $col)
            {
                $entry[] =  $col->run($row);
            }
            return $entry;
        });
    }

    public function setSearchStrip()
    {
        $this->options['stripSearch'] = true;
    }

    public function setOrderStrip()
    {
        $this->options['stripOrder'] = true;
    }

}
