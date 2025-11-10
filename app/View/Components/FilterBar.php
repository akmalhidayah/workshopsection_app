<?php 

namespace App\View\Components;

use Illuminate\View\Component;

class FilterBar extends Component
{
    public $search;
    public $searchPlaceholder;
    public $statusOptions;
    public $dateFilter;
    public $entriesOptions;

    public function __construct(
        $search = false,
        $searchPlaceholder = 'Cari data...',
        $statusOptions = [],
        $dateFilter = false,
        $entriesOptions = [5,10,25,50,100]
    ) {
        $this->search = $search;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->statusOptions = $statusOptions;
        $this->dateFilter = $dateFilter;
        $this->entriesOptions = $entriesOptions;
    }

    public function render()
    {
        return view('components.filter-bar');
    }
}
