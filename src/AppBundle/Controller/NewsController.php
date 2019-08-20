<?php


namespace AppBundle\Controller;


use AppBundle\Model\Product\AbstractProduct;
use AppBundle\Website\LinkGenerator\NewsLinkGenerator;
use AppBundle\Website\Navigation\BreadcrumbHelperService;
use AppBundle\Website\Tool\ForceInheritance;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Templating\Helper\HeadTitle;
use Pimcore\Templating\Helper\Placeholder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Paginator\Paginator;
use Pimcore\Model\DataObject\News;

class NewsController extends BaseController
{

    const NEWS_DEFAULT_DOCUMENT_PROPERTY_NAME = 'news_default_document';

    public function listingAction(Request $request) {

        // get a list of news objects and order them by date
        $newsList = new News\Listing();
        $newsList->setOrderKey('date');
        $newsList->setOrder('DESC');

        $paginator = new Paginator($newsList);
        $paginator->setCurrentPageNumber($request->get('page'));
        $paginator->setItemCountPerPage(6);

        $this->view->news = $paginator;
        $this->view->paginationVariables = $paginator->getPages('Sliding');

    }

    /**
     * @param Request $request
     * @Route("{path}/{newstitle}~n{news}", name="news-detail", defaults={"path"=""}, requirements={"path"=".*?", "newstitle"="[\w-]+", "news"="\d+"})
     */
    public function detailAction(Request $request, HeadTitle $headTitleHelper, Placeholder $placeholderHelper, NewsLinkGenerator $newsLinkGenerator, BreadcrumbHelperService $breadcrumbHelperService) {

        $news = News::getById($request->get('news'));

        if(!($news instanceof News && $news->isPublished())) {
            throw new NotFoundHttpException("News not found.");
        }

        $headTitleHelper($news->getTitle());
        $breadcrumbHelperService->enrichNewsPage($news);

        $placeholderHelper('canonical')->set($newsLinkGenerator->generate($news, ['document' => $this->document->getProperty(self::NEWS_DEFAULT_DOCUMENT_PROPERTY_NAME)] ));

        $this->view->news = $news;


    }

    public function newsTeaserAction(Request $request)
    {
        $paramsBag = [];
        if ($request->get('type') == 'object') {

            $news = News::getById($request->get('id'));
            $paramsBag['news'] = $news;
            return $this->render('news/news_teaser.html.twig', $paramsBag);
        }

        throw new NotFoundHttpException('News not found.');
    }


}