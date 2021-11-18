<?php

declare(strict_types=1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostPerUser extends AbstractCalculator
{

    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $userPosts = [];



    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        // Noops!
        $authorId = $postTo->getAuthorId();
        if (!empty($authorId)) {
            if (isset($this->userPosts[$authorId]))
                $this->userPosts[$authorId]++;
            else
                $this->userPosts[$authorId] = 1;
        }
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {

        $value = count($this->userPosts) > 0
            ? array_sum($this->userPosts) / count($this->userPosts)
            : 0;

        return (new StatisticsTo())->setValue(round($value, 2));
    }
}
