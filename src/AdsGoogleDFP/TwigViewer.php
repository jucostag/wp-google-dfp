<?php
namespace AdsGoogleDFP;

class TwigViewer
{
    /**
     * @var string
     */
    const VIEWS_PATH = __DIR__ . '/../../views';

    /**
     * @param string $view      Arquivo HTML será renderizado
     * @param array $content    Conteúdo a ser renderizado no HTML especificado
     * @return string           HTML tratado com as variaveis previamente substituidas
     */
    public static function render($view, $content)
    {
        require __DIR__ . '/../../vendor/autoload.php';

        $loader = new \Twig_Loader_Filesystem(self::VIEWS_PATH);
        $twig = new \Twig_Environment($loader);
        
        return $twig->render($view, $content);
    }
}