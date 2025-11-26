<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ReceiptLayoutPreferences extends Component
{
    public $layout = [];

    public $previewImage = '';
    public $previewWidth;
    public $previewHeight;

    protected $defaults;

    public function mount()
    {
        $this->loadDefaults();
        $this->loadUserPreferences();
        $this->updateAll();
    }

    /**
     * Load base defaults from config file
     */
    protected function loadDefaults()
    {
        $this->defaults = [
            'receipt_type'   => config('defaults.receipt_type'),
            'office_name'    => config('defaults.office_name'),
            'date'           => config('defaults.date'),
            'payor_info'     => config('defaults.payor_info'),
            'particulars'    => config('defaults.particulars'),
            'total'          => config('defaults.total'),
            'amount_words'   => config('defaults.amount_words'),
            'cashier_name'   => config('defaults.cashier_name'),
        ];
    }

    /**
     * Merge user saved preferences over defaults
     */
    protected function loadUserPreferences()
    {
        $existing = Auth::user()->receiptLayout
            ? Auth::user()->receiptLayout->toArray()
            : [];

        // Merge DB values over config defaults
        $this->layout = array_replace_recursive($this->defaults, $existing);
    }

    /**
     * Convert mm into pixels for the preview container
     */
    protected function mmToPx($mm): int
    {
        return round((float) $mm * 3.78); // 1 mm ≈ 3.78 px
    }

    /**
     * Compute preview width and height in px
     */
    protected function updatePreviewDimensions()
    {
        $widthMm  = (float) str_replace('mm', '', $this->layout['page_width'] ?? '90');
        $heightMm = (float) str_replace('mm', '', $this->layout['page_height'] ?? '188');

        $this->previewWidth  = $this->mmToPx($widthMm);
        $this->previewHeight = $this->mmToPx($heightMm);
    }

    /**
     * Update page sizes depending on receipt type
     */
    protected function updatePageSize()
    {
        $type = $this->layout['receipt_type'] ?? $this->defaults['receipt_type'];

        $page = config("defaults.page.$type") ?? [
            'width'  => '90mm',
            'height' => '188mm',
        ];

        $this->layout['page_width']  = $page['width'];
        $this->layout['page_height'] = $page['height'];
    }

    /**
     * Pick preview image based on selected type
     */
    protected function updatePreviewImage()
    {
        $this->previewImage = match ($this->layout['receipt_type']) {
            'booklet_form51' => asset('receipts/form51_booklet.png'),
            default          => asset('receipts/form51_continuous.png'),
        };
    }

    /**
     * Update all reactive components based on new state
     */
    protected function updateAll()
    {
        $this->updatePageSize();
        $this->updatePreviewDimensions();
        $this->updatePreviewImage();
    }

    /**
     * Listen to ALL updates inside layout.*
     * Then auto-update preview + auto-save
     */
    public function updated($property)
    {
        if (!str_starts_with($property, 'layout.')) {
            return;
        }

        $this->updateAll();
        $this->saveLayout();
    }

    /**
     * Auto-save layout to DB
     */
    public function saveLayout()
    {
        Auth::user()->receiptLayout()->updateOrCreate(
            ['user_id' => Auth::id()],
            $this->layout
        );
    }

    /**
     * NEW: Reset layout based only on **current receipt type**
     */
    public function resetToDefault()
    {
        $type = $this->layout['receipt_type'] ?? $this->defaults['receipt_type'];

        // Reload all defaults according to config still
        $this->layout = array_replace_recursive(
            $this->defaults,
            [
                'receipt_type' => $type, // Keep current selection
            ]
        );

        // Update preview + save changes
        $this->updateAll();
        $this->saveLayout();

        session()->flash('message', 'Receipt layout reset to default.');
    }

    public function render()
    {
        return view('livewire.receipt-layout-preferences');
    }
}
