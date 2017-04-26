<?php

namespace Transformers;

class DiscogsTransformer extends Transformer
{
    /**
     * transform discogs
     *
     * @param array $discogs
     * @return array
     */
    public function transform($discogs)
    {
        return [
            'title' => $discogs->basic_information->title,
            'artist' => $discogs->basic_information->artists[0]->name,
        ];
    }
}
