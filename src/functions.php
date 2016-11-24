<?php

if (! function_exists('transform')) {
    /**
     * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection $data
     * @param \LukeVear\LaravelTransformer\AbstractTransformer|null $transformer
     * @return \LukeVear\LaravelTransformer\TransformerEngine
     */
    function transform($data, $transformer = null)
    {
        return new \LukeVear\LaravelTransformer\TransformerEngine($data, $transformer);
    }
}