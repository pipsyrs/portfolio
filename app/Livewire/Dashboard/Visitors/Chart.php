<?php

namespace App\Livewire\Dashboard\Visitors;

use App\Models\Visitor;
use Livewire\Attributes\Url;
use Livewire\Component;

class Chart extends Component
{
    #[Url(as: 'range', keep: false)]
    public int $days = 7;

    /** Rentang yang diizinkan — mencegah nilai sembarang dari query string. */
    private const RANGES = [7, 30, 90];

    public function mount(): void
    {
        if (! in_array($this->days, self::RANGES, true)) {
            $this->days = 7;
        }
    }

    public function setRange(int $days): void
    {
        if (in_array($days, self::RANGES, true)) {
            $this->days = $days;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.visitors.chart', [
            'chart' => $this->series(),
            'ranges' => self::RANGES,
        ]);
    }

    /**
     * Satu query yang dikelompokkan, lalu dipetakan ke deret tanggal penuh —
     * menghindari satu query per hari seperti implementasi widget lama.
     */
    private function series(): array
    {
        $start = now()->subDays($this->days - 1)->startOfDay();

        $counts = Visitor::query()
            ->where('visited_date', '>=', $start->toDateString())
            ->selectRaw('visited_date, COUNT(*) as total')
            ->groupBy('visited_date')
            ->pluck('total', 'visited_date');

        $labels = [];
        $data = [];

        for ($i = 0; $i < $this->days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();

            $labels[] = $this->days > 31 ? $date->format('d M') : $date->format('D, d M');
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'peak' => $data === [] ? 0 : max($data),
            'average' => $data === [] ? 0 : round(array_sum($data) / count($data), 1),
        ];
    }
}
