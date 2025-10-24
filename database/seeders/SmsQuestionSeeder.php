<?php

namespace Database\Seeders;

use App\Models\SmsQuestion;
use Illuminate\Database\Seeder;

class SmsQuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'question' => 'How satisfied are you with our service?',
                'options' => ['1 - Very Dissatisfied', '2 - Dissatisfied', '3 - Neutral', '4 - Satisfied', '5 - Very Satisfied'],
            ],
            [
                'question' => 'Would you recommend us to a friend?',
                'options' => ['1 - Definitely not', '2 - Probably not', '3 - Maybe', '4 - Probably yes', '5 - Definitely yes'],
            ],
            [
                'question' => 'How easy was it to use our service?',
                'options' => ['1 - Very difficult', '2 - Difficult', '3 - Neutral', '4 - Easy', '5 - Very easy'],
            ],
            [
                'question' => 'How would you rate our customer support?',
                'options' => ['1 - Poor', '2 - Fair', '3 - Good', '4 - Very good', '5 - Excellent'],
            ],
        ];

        foreach ($questions as $questionData) {
            SmsQuestion::create($questionData);
        }
    }
}
