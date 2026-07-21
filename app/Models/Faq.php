<?php

class Faq extends Model
{
    public static function all(): array
    {
        return self::db()->query('SELECT * FROM faqs ORDER BY sort_order ASC')->fetchAll();
    }
}
