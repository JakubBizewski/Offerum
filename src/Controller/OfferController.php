<?php

namespace Offerum\Controller;

use Offerum\Entity\Offer;
use Offerum\Form\OfferType;
use Offerum\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class OfferController extends AbstractController
{
    private $offerRepository;

    public function __construct(OfferRepository $offerRepository)
    {
        $this->offerRepository = $offerRepository;
    }

    public function index(int $page)
    {
        $offerCount = $this->offerRepository->countActive();
        $offers = $this->offerRepository->findAllActive(8, $page);

        return $this->render('offer/index.html.twig', [
            'currentPage' => $page,
            'offerCount' => $offerCount,
            'offers' => $offers
        ]);
    }

    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $offer = new Offer();
        $form = $this->createForm(OfferType::class, $offer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $offer->setAuthor($this->getUser());
            $offer->setCreateDate(new \DateTime());
            $offer->setActive(true);

            $this->offerRepository->save($offer);

            return $this->redirectToRoute('offer.index');
        }

        return $this->render('offer/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function edit(Request $request, int $id)
    {
        $offer = $this->offerRepository->find($id);

        if (!$offer) {
            throw $this->createNotFoundException('Offer with ID ' . $id . ' not found');
        }

        if ($offer->getAuthor() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(OfferType::class, $offer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->offerRepository->save($offer);

            return $this->redirectToRoute('user.my_offers');
        }

        return $this->render('offer/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function delete(int $id)
    {
        $offer = $this->offerRepository->find($id);

        if (!$offer) {
            throw $this->createNotFoundException('Offer with ID ' . $id . ' not found');
        }

        if ($offer->getAuthor() != $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $this->offerRepository->delete($offer);

        return $this->redirectToRoute('user.my_offers');
    }

    public function show(int $id)
    {
        $offer = $this->offerRepository->find($id);

        if (!$offer) {
            throw $this->createNotFoundException('Offer with ID ' . $id . ' not found');
        }

        return $this->render('offer/show.html.twig', [
            'offer' => $offer
        ]);
    }
}