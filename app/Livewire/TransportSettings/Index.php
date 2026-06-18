<?php

namespace App\Livewire\TransportSettings;

use App\Models\TransportSetting;
use Livewire\Component;

class Index extends Component
{
    public string $check_in_start = '06:45';
    public string $check_in_end = '07:15';
    public string $check_out_start = '12:45';
    public string $check_out_end = '13:15';
    public int $amount = 0;
    public bool $is_active = true;

    public function mount(): void
    {
        $setting = TransportSetting::first();

        if ($setting) {
            $this->check_in_start = substr($setting->check_in_start, 0, 5);
            $this->check_in_end = substr($setting->check_in_end, 0, 5);
            $this->check_out_start = substr($setting->check_out_start, 0, 5);
            $this->check_out_end = substr($setting->check_out_end, 0, 5);
            $this->amount = (int) $setting->amount;
            $this->is_active = (bool) $setting->is_active;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'check_in_start' => ['required'],
            'check_in_end' => ['required', 'after:check_in_start'],
            'check_out_start' => ['required'],
            'check_out_end' => ['required', 'after:check_out_start'],
            'amount' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        TransportSetting::updateOrCreate(
            ['id' => 1],
            $data
        );

        session()->flash('success', 'Pengaturan transport berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.transport-settings.index')
            ->layout('layouts.app');
    }
}
