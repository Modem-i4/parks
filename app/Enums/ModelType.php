<?php

namespace App\Enums;

enum ModelType: string
{
    case Bush               = 'Bush';
    case Family             = 'Family';
    case Flower             = 'Flower';
    case Genus              = 'Genus';
    case Green              = 'Green';
    case Hedge              = 'Hedge';
    case HedgeRow           = 'HedgeRow';
    case HedgeShape         = 'HedgeShape';
    case Infrastructure     = 'Infrastructure';
    case InfrastructureType = 'InfrastructureType';
    case Marker             = 'Marker';
    case MarkersTag         = 'MarkersTag';
    case Media              = 'Media';
    case MediaLibrary       = 'MediaLibrary';
    case News               = 'News';
    case Park               = 'Park';
    case Plot               = 'Plot';
    case Recommendation     = 'Recommendation';
    case Species            = 'Species';
    case Tag                = 'Tag';
    case Tree               = 'Tree';
    case User               = 'User';
    case Work               = 'Work';

    public function label(): string
    {
        return match($this) {
            self::Bush               => 'Кущ',
            self::Family             => 'Родина',
            self::Flower             => 'Квітка',
            self::Genus              => 'Рід',
            self::Green              => 'Зелені насадження',
            self::Hedge              => 'Жива огорожа',
            self::HedgeRow           => 'Ряд огорожі',
            self::HedgeShape         => 'Форма огорожі',
            self::Infrastructure     => 'Інфраструктура',
            self::InfrastructureType => 'Тип інфраструктури',
            self::Marker             => 'Маркер',
            self::MarkersTag         => 'Тег мітки',
            self::Media              => 'Медіа',
            self::MediaLibrary       => 'Медіатека',
            self::News               => 'Новина',
            self::Park               => 'Парк',
            self::Plot               => 'Ділянка',
            self::Recommendation     => 'Рекомендація',
            self::Species            => 'Вид',
            self::Tag                => 'Тег',
            self::Tree               => 'Дерево',
            self::User               => 'Користувач',
            self::Work               => 'Робота',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}
