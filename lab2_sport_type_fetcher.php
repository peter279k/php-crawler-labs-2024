<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\JavascriptException;
use Symfony\Component\DomCrawler\Crawler;


echo 'Ensuring the locale should be zh_TW' . PHP_EOL;
echo 'Fething main type and type has been started.', PHP_EOL;


$reqUrl = 'https://www.data-sports.tw/#/SportData/Service';
$browserOptions = [
    'headless' => false,
    'noSandbox' => true,
    'ignoreCertificateErrors' => true,
    'disableNotifications' => true,
    'windowSize' => [1920, 1080],
];

try {
    $browserFactory = new BrowserFactory('google-chrome-stable');

    $browser = $browserFactory->createBrowser($browserOptions);

    $page = $browser->createPage();
    $page->navigate($reqUrl)->waitForNavigation();

    sleep(random_int(5, 10));

    $jsCode = 'document.documentElement.innerHTML';
    $contents = $page->evaluate($jsCode)->getReturnValue();
    $crawler = new Crawler($contents);
    $tabInfo = [];
    $index = 0;
    $crawler->filter('div[role="tab"]')->reduce(function(Crawler $node, $i) {
        global $index;
        global $tabInfo;
        if ($node->attr('id') !== null && substr($node->attr('id'), 0, 3) === 'tab') {
            $tabInfo[$index] = [
                'MainType' => $node->text() . '(' . substr($node->attr('id'), 4) . ')',
                'Type' => [],
                'SubType' => [],
            ];
            $index++;
        }
    });

    $index = 0;
    $crawler->filter('div[role="button"]')->reduce(function(Crawler $node, $i) use($page) {
        global $index;
        global $tabInfo;

        $buttonId = null;

        if ($index === count($tabInfo)) {
        } else {
            $buttonId = $node->attr('id');
            if ($buttonId !== null && $index > 0) {
                $jsCode = sprintf('document.querySelector(\'div[id="%s"]\').click()', $buttonId);
                $page->evaluate($jsCode)->getReturnValue();
                sleep(5);
            }

            $buttonItem = 'el-button datatype-button el-button--button activeButton';
            $buttonItem2 = 'el-button datatype-button el-button--button';
            $contents = $page->evaluate('document.documentElement.innerHTML')->getReturnValue();
            $clickedCrawler = new Crawler($contents);

            $clickedCrawler->filter(sprintf('button[class="%s"]', $buttonItem))->reduce(function(Crawler $node, $i) {
                global $index;
                global $tabInfo;

                $btnText = $node->text() . '(' . $node->attr('label') . ')';
                $btnText = str_replace(' ', '', $btnText);
                if (!in_array($btnText, $tabInfo[$index]['Type'])) {
                    $tabInfo[$index]['Type'][] = $btnText;
                }
            });

            $clickedCrawler->filter(sprintf('button[class="%s"]', $buttonItem2))->reduce(function(Crawler $node, $i) {
                global $index;
                global $tabInfo;

                $btnText = $node->text() . '(' . $node->attr('label') . ')';
                $btnText = str_replace(' ', '', $btnText);
                if (!in_array($btnText, $tabInfo[$index]['Type'])) {                                                            $tabInfo[$index]['Type'][] = $btnText;
                }
            });
        }

        if ($buttonId !== null) {
            $index++;
        }
    });

    $page->evaluate('location.reload()');
    sleep(5);

    $index = 0;
    $buttonIds = [];

    $contents = $page->evaluate('document.documentElement.innerHTML')->getReturnValue();
    $crawler = new Crawler($contents);
    $crawler->filter('div[role="button"]')->reduce(function(Crawler $node, $i) use($page) {
        global $index;
        global $buttonIds;
        $buttonId = $node->attr('id');
        if ($buttonId !== null) {
            $buttonIds[] = $buttonId;
            $index++;
        }
    });

    $index = 0;
    $subIndex = 0;
    while (true) {
        if ($index >= count($tabInfo)) {
            break;
        } else {
            $tabInfo[$index]['SubType'][$subIndex] = [];

            $contents = $page->evaluate('document.documentElement.innerHTML')->getReturnValue();                        $collapsedItemCrawler = new Crawler($contents);

            $subTypeButtonItem = 'el-button subtype-button el-button--button activeButton';
            $subTypeButtonItem2 = 'el-button subtype-button el-button--button';
            $collapsedItemCrawler->filter(sprintf('button[class="%s"]', $subTypeButtonItem))->reduce(function(Crawler $node, $i) {
                global $index;
                global $page;
                global $subIndex;
                global $tabInfo;

                $tabInfo[$index]['SubType'][$subIndex][] = $node->text();
            });

            $collapsedItemCrawler->filter(sprintf('button[class="%s"]', $subTypeButtonItem2))->reduce(function(Crawler $node, $i) {
                global $index;
                global $page;
                global $subIndex;
                global $tabInfo;

                $tabInfo[$index]['SubType'][$subIndex][] = $node->text();
            });

            $subIndex++;
            if ($subIndex === count($tabInfo[$index]['Type'])) {
                $subIndex = 0;
                $index++;
                $buttonId = $buttonIds[$index];
                if ($buttonId !== null && $index > 0 && $index < count($tabInfo)) {                                             $jsCode = sprintf('document.querySelector(\'div[id="%s"]\').click()', $buttonId);                           $page->evaluate($jsCode)->getReturnValue();                                                                 sleep(5);                                                                                               }
            } else {
                preg_match_all('/(\w+)/', $tabInfo[$index]['Type'][$subIndex], $matched);
                $subTypeName = $matched[0][0]; 
                $jsCode = sprintf(
                    'document.querySelector(\'button[label="%s"],button[value="%s"]\').click()',
                    $subTypeName, $subTypeName
                );
               $page->evaluate($jsCode)->getReturnValue();
               sleep(5);
            }
        }
    }
    $browser->close();

    $fileName = 'sport_types.json';
    $tabInfoJsonString = json_encode($tabInfo);
    file_put_contents($fileName, $tabInfoJsonString);

    echo sprintf('The %s file is saved!', $fileName), PHP_EOL;
} catch(JavascriptException $e) {
    var_dump($e->getMessage());
    echo 'Evaluating JavaScript is failed...Exited.', PHP_EOL;
    exit(1);
}
