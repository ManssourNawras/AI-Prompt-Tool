<?php


namespace App\Traits;
use Closure;

trait OnProgressHookTrait
{
    public ?Closure $onProgressFn = null;

    public function onProgress(Closure $fn): self
    {
        $this->onProgressFn = $fn;

        return $this;
    }

    public function callOnProgressHook(...$args): void
    {
        if ($this->onProgressFn) {
            ($this->onProgressFn)(...$args);
        }
    }

}