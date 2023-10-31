<?php

namespace LearnToWin\GeneralBundle\Constants;

class GroupContext
{
    public const USER_READ = 'user:read';
    public const USER_WRITE = 'user:write';
    public const USER_NOTIFICATION = 'user:notification';

    public const ORGANIZATION_READ = 'organization:read';
    public const ORGANIZATION_WRITE = 'organization:write';
    public const ORGANIZATION_NOTIFICATION = 'organization:notification';

    public const TRACKED_READ = 'tracked:read';
    public const TRACKED_WRITE = 'tracked:write';

    public const OWNED_READ = 'owned:read';
    public const OWNED_WRITE = 'owned:write';

    public const CARD_BULK_READ = 'card:bulk:read';
    public const CARD_BULK_WRITE = 'card:bulk:write';
    public const CARD_READ = 'card:read';
    public const CARD_WRITE = 'card:write';

    public const COURSE_READ = 'course:read';
    public const COURSE_WRITE = 'course:write';

    public const LEARNING_ITEM_READ = 'learning_item:read';
    public const LEARNING_ITEM_WRITE = 'learning_item:write';

    public const MEDIA_READ = 'media:read';
    public const MEDIA_WRITE = 'media:write';
}
