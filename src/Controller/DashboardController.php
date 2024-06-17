<?php

namespace App\Controller;

use App\Model\DataObject\User;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\TodoItem;
use Pimcore\Model\Element\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends FrontendController
{
    #[Route( '/auth/dashboard', name: 'dashboard' )]
    #[IsGranted( 'ROLE_USER' )]
    public function defaultAction( Request $request ): Response{
        $user = $this->getUser();
        if( !$user instanceof User ){
            // error -> redirect to Login with Flash Message
            $this->addFlash( 'error', 'No User found' );
            return $this->redirectToRoute( 'simple_auth_login' );
        }

        $todoItems = new TodoItem\Listing();
        $todoItems->addConditionParam( 'parentId = :parentId', [
            'parentId' => $user->getId()
        ] );
        $todoItems->setOrderKey( 'creationDate' );
        $todoItems->setOrder( 'DESC' );

        return $this->render( 'Dashboard/index.html.twig', [
            'todoItems' => $todoItems
        ] );
    }

    #[Route( '/auth/dashboard/add', name: 'dashboard_add', methods: [ 'POST' ] )]
    public function addAction( Request $request ){
        $user = $this->getUser();
        if( !$user instanceof User ){
            // error -> redirect to Login with Flash Message
            $this->addFlash( 'error', 'No User found' );
            return $this->redirectToRoute( 'simple_auth_login' );
        }

        if( $request->get( 'task', '' ) != '' ){
            // add task to DB
            $todoItem = new TodoItem();
            $todoItem->setParentId( $user->getId() );
            $todoItem->setKey( Service::getValidKey( $request->get( 'task' ) . '-' . crc32( uniqid() ), 'object' ) );
            $todoItem->setPublished( true );

            $todoItem->setTaskName( $request->get( 'task' ) );
            $todoItem->setUser( $this->getUser() );

            $todoItem->save();

            return $this->redirectToRoute( 'dashboard' );
        }

        $this->addFlash( 'error', 'Task cannot be empty' );
        return $this->redirectToRoute( 'dashboard' );
    }

    #[Route( '/auth/dashboard/done/{id}', name: 'dashboard_done' )]
    public function doneAction( Request $request, int $id ): Response{
        $user = $this->getUser();
        if( !$user instanceof User ){
            // error -> redirect to Login with Flash Message
            $this->addFlash( 'error', 'No User found' );
            return $this->redirectToRoute( 'simple_auth_login' );
        }

        $todotItem = TodoItem::getById( $id );
        if( !$todotItem instanceof TodoItem ){
            $this->addFlash( 'error', 'No Task found' );
            return $this->redirectToRoute( 'dashboard' );
        }else{
            $todotItem->setIsDone( !$todotItem->getIsDone() );
            $todotItem->save();
        }

        return $this->json( [
            'success' => true,
            'url' => $this->generateUrl( 'dashboard' )
        ] );
    }


    #[Route( '/auth/dashboard/delete/{id}', name: 'dashboard_delete' )]
    public function deleteAction( Request $request, int $id ): Response{
        $user = $this->getUser();
        if( !$user instanceof User ){
            // error -> redirect to Login with Flash Message
            $this->addFlash( 'error', 'No User found' );
            return $this->redirectToRoute( 'simple_auth_login' );
        }

        $todotItem = TodoItem::getById( $id );
        if( $todotItem instanceof TodoItem ){
            $todotItem->delete();
        }

        return $this->redirectToRoute( 'dashboard' );
    }

    #[Route( '/auth/dashboard/filter/{filter}', name: 'dashboard_filter' )]
    public function filterAction( Request $request, string $filter ): Response{
        $user = $this->getUser();
        if( !$user instanceof User ){
            // error -> redirect to Login with Flash Message
            $this->addFlash( 'error', 'No User found' );
            return $this->redirectToRoute( 'simple_auth_login' );
        }

        $todoItems = new TodoItem\Listing();
        $todoItems->addConditionParam( 'parentId = :parentId', [
            'parentId' => $this->getUser()->getId()
        ] );

        if( $filter == 'done' ){
            $todoItems->addConditionParam( 'isDone = 1' );
        }elseif( $filter == 'not-done' ){
            $todoItems->addConditionParam( 'isDone = 0' );
        }

        $todoItems->setOrderKey( 'creationDate' );
        $todoItems->setOrder( 'DESC' );

        return $this->render( 'Dashboard/index.html.twig', [
            'todoItems' => $todoItems,
            'highlight' => $filter
        ] );

    }

}
