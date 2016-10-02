<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\wordTriedType;
use AppBundle\Game\GameContext;
use AppBundle\Game\GameRunner;
use AppBundle\Game\Loader\TextFileLoader;
use AppBundle\Game\WordList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/game")
 */
class GameController extends Controller
{
    /**
     * @Route("/", name="app_game_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        //Création du forulaire
        $form = $this->createForm(wordTriedType::class,
                array(),
                array(
            'action' => $this->generateUrl('app_game_try_word'),
            'method' => 'POST',
                )
        );
        
        //Création du Game
        $gr = $this->createGameRunner($request->getSession());
        $game= $gr->loadGame();
        //var_dump($game->isHanged());
       return ['game'=> $game,
                'form'=> $form->createView(),
           ];
    }
    
    /**
     * @param Request $request
     * 
     * @Route("/try/{letter}", requirements={"letter"="[a-z]"}, name="app_game_try_letter")
     */
    public  function tryLetterAction(Request $request, $letter)
    {
        $gr = $this->createGameRunner($request->getSession());
        $game = $gr->playLetter($letter);
        
        if($game->isWon()){
            return $this->redirectToRoute('app_game_success');
        }
        
        if($game->isHanged()){
            return $this->redirectToRoute('app_game_failed');
        }
        
        return $this->redirectToRoute('app_game_index');
    }
    
    /**
     * @Route("/try", name="app_game_try_word")
     * @Method("POST")
     */
    public  function tryWordAction(Request $request)
    {
        $form = $this->createForm(wordTriedType::class);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            //verifier le mot
        $gr = $this->createGameRunner($request->getSession());
        $game = $gr->playWord($form->getData()['word_tried']);           
        //mettre fin à la partie (bon ou pas)
         if($game->isWon()){
            return $this->redirectToRoute('app_game_success');
        }
        
        if($game->isHanged()){
            return $this->redirectToRoute('app_game_failed');
        }
        }
//        $gr->playWord($request->)
        //return $this->redirectToRoute('app_game_index');
    }
    
    /**
     * @Route("/reset", name="app_game_reset")
     * @param \AppBundle\Controller\Request $request
     */
    public function resetAction(Request $request)
    {
        $gr = $this->createGameRunner($request->getSession());
        $gr->resetGame();
        
        return $this->redirectToRoute('app_game_index');
    }
    
    /**
     * @param Request $request
     * @return array
     * 
     * @Template()
     * @Route("/success", name="app_game_success")
     */
    public function successAction(Request $request)
    {
        $gr = $this->createGameRunner($request->getSession());
        $game = $gr->resetGameOnSuccess();
        return ['game' => $game];
    }
    
    /**
     * @param Request $request
     * @return array
     * 
     * @Route("/failed", name="app_game_failed")
     * @Template()
     */
    public function failedAction(Request $request)
    {
        $gr = $this->createGameRunner($request->getSession());
        $game = $gr->resetGameOnFailure();
        return ['game' => $game];
    }


    /**
     * Fonction privée pour factoriser l'utilisation l'instance de gamerunner
     * @param SessionInterface $session
     * @return GameRunner
     */
    private function createGameRunner(SessionInterface $session) {
        $gc = new GameContext($session);
        $wl= new WordList();
        $wl->addLoader('txt', new TextFileLoader());
        $wl->loadDictionaries(array(
            $this->getParameter('kernel.root_dir').'/../data/words.txt'
        ));
        return new GameRunner($gc, $wl);
        
    }
}
