<?php
declare(strict_types=1);

namespace App\Models\Transformers\Item;

use App\Models\Transformers\Transformer;

/**
 * Transform the data from our queries into the format we want to display
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough 2018-2020
 * @license https://github.com/costs-to-expect/api/blob/master/LICENSE
 */
class Game extends Transformer
{
    public function format(array $to_transform): void
    {
        $game = [];
        $statistics = [];

        try {
            if (array_key_exists('item_game', $to_transform)) {
                $game = json_decode($to_transform['item_game'], true, 512, JSON_THROW_ON_ERROR);
            }
            if (array_key_exists('item_statistics', $to_transform)) {
                $statistics = json_decode($to_transform['item_statistics'], true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\JsonException $e) {
            $game = [
                'error' => 'Unable to decode scores'
            ];
       }

       $winner = null;
       if (array_key_exists('item_winner_id', $to_transform) && array_key_exists('item_winner_name', $to_transform)) {
            $winner = [
                'id' => $this->hash->category()->encode($to_transform['item_winner_id']),
                'name' => $to_transform['item_winner_name']
            ];
       }

        $this->transformed = [
            'id' => $this->hash->item()->encode($to_transform['item_id']),
            'name' => $to_transform['item_name'],
            'description' => $to_transform['item_description'],
            'game' => $game,
            'statistics' => $statistics,
            'winner' => $winner,
            'score' => $to_transform['item_score'],
            'complete' => $to_transform['item_complete'],
            'created' => $to_transform['item_created_at'],
            'updated' => $to_transform['item_updated_at']
        ];
    }
}
