<?php

declare(strict_types=1);

namespace Tests\unit;

use DateTime;
use PDO;
use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostPerUser;
use Statistics\Builder\ParamsBuilder;


/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class TestTest extends TestCase
{

    private const POST_CREATED_DATE_FORMAT = DateTime::ATOM;
    /**
     * @test
     */
    public function testNothing(): void
    {
        $this->assertTrue(true);
    }

   
    //Tested avarage with answer which is defined in phpunit config file.
    public function testAvaragePostPerUserPerMonth(): void
    {


        $filePath = $_ENV['SOCIAL_POST_RESPONSE_PATH'];
        $month = $_ENV['SOCIAL_POST_MONTH'];
        $expectedMean = $_ENV['USER_PER_MONTH_EXPECTED_MEAN'];

        $date  = DateTime::createFromFormat('F, Y', $month);
        $parameters = ParamsBuilder::reportStatsParams($date);
        $jsonArray = json_decode(@file_get_contents($filePath, true));

        foreach ($parameters as $paramsTo) {
            $statName = $paramsTo->getStatName();
            if ($statName == 'average-posts-per-user') {
                $params = $paramsTo;
                break;
            }
        }


        $posts = $jsonArray->data->posts;
        $avePosts = new AveragePostPerUser();
        $avePosts->setParameters($params);
        foreach ($posts as $post) {

            $date = DateTime::createFromFormat(
                self::POST_CREATED_DATE_FORMAT,
                $post->created_time
            );


            $dto = (new SocialPostTo())
                ->setId($post->id ?? null)
                ->setAuthorName($post->from_name ?? null)
                ->setAuthorId($post->from_id ?? null)
                ->setText($post->message ?? null)
                ->setType($post->type ?? null)
                ->setDate($date ?? null);

                $avePosts->accumulateData($dto);
        }

        $mean = $avePosts->calculate()->getValue();
        $this->assertTrue($mean == $expectedMean);
    }
}
