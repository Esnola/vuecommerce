<?php

use Livewire\Component;

new class extends Component {
    public string $locale = 'en';

    public function mount(): void
    {
        $this->locale = session('locale', app()->getLocale() ?: 'en');
    }

    public function toggle(): void
    {
        $this->locale = $this->locale === 'en' ? 'es' : 'en';

        session(['locale' => $this->locale]);
        app()->setLocale($this->locale);

        $this->redirect(url()->previous(), navigate: false);
    }

    public function render()
    {
        return view('components.partials.language-toggle');
    }
};
?>
