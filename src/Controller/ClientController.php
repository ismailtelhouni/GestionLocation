<?php

namespace App\Controller;

use App\Entity\AReservation;
use App\Form\AReservationType;
use App\Repository\AReservationRepository;
use App\Repository\AAnnonceRepository;
use App\Services\MailerService;
use App\Service\PdfService;
use mPDF;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Knp\Snappy\Pdf;


#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('/' , name: 'client_index', methods: ['GET'])]
    public function index(AReservationRepository $aReservationRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_home');
        }
        return $this->render('client/clienthome.html.twig', [
            'a_reservations' => $this->getUser()->getAClient()->getCreservations(),
        ]);
    }



    #[Route('/email' , name: 'email', methods: ['GET'])]
    public function mail(AReservationRepository $aReservationRepository, MailerService $mailer): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_home');
        }

        $mailer->sendEmail();
        return $this->render('client/clienthome.html.twig', [
            'a_reservations' => $this->getUser()->getAClient()->getCreservations(),
        ]);
    }




    #[Route('/pdf' , name: 'client_pdf', methods: ['GET'])]
    public function generatePdf(PdfService $pdf)
    {
        $content = "<html>first pdf</html>";
        $html = $this->render('client/a_reservation/pdf_generator/index.html.twig', [
            'content' => $content,
        ]);

        $pdf->showPdfFile($html);

        // $html="<html>hello world</html>";
        // $pdf->showPdfFile($html);

        // $pdf = new mPDF();

        // $pdf->WriteHTML($content);
        // $contrat = $pdf->Output('','S');
        // //echo $contrat;
        // return $this->render('client/a_reservation/pdf_generator/index.html.twig', [
        //     'content' => $content,
        // ]);
    }






    #[Route('reservations/', name: 'client_reservation_index', methods: ['GET'])]
    public function reservations(AReservationRepository $aReservationRepository,AAnnonceRepository $aAnnonceRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_home',[
                'user'=>$this->getUser(),
            ]);
        }

        return $this->render('client/a_reservation/index.html.twig', [
            'a_reservations' => $this->getUser()->getAClient()->getCreservations(),
            'a_annonces'=> $aAnnonceRepository->findAll(),
            'user'=>$this->getUser(),
        ]);
    }

    #[Route('reservations/new', name: 'client_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AReservationRepository $aReservationRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_home');
        }

        $aReservation = new AReservation();
        $form = $this->createForm(AReservationType::class, $aReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $aReservationRepository->save($aReservation, true);

            return $this->redirectToRoute('client_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/a_reservation/new.html.twig', [
            'a_reservation' => $aReservation,
            'form' => $form,
        ]);
    }

    #[Route('reservations/{id}', name: 'client_reservation_show', methods: ['GET'])]
    public function show(AReservation $aReservation): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_home');
        }

        return $this->render('client/a_reservation/show.html.twig', [
            'a_reservation' => $aReservation,
        ]);
    }

    #[Route('reservations/{id}/edit', name: 'client_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AReservation $aReservation, AReservationRepository $aReservationRepository): Response
    {

        if(!$this->getUser()){
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(AReservationType::class, $aReservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $aReservationRepository->save($aReservation, true);

            return $this->redirectToRoute('client_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/a_reservation/edit.html.twig', [
            'a_reservation' => $aReservation,
            'form' => $form,
        ]);
    }

    #[Route('reservations/{id}', name: 'client_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, AReservation $aReservation, AReservationRepository $aReservationRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_home');
        }

        if ($this->isCsrfTokenValid('delete'.$aReservation->getId(), $request->request->get('_token'))) {
            $aReservationRepository->remove($aReservation, true);
        }

        return $this->redirectToRoute('client_reservation_index', [], Response::HTTP_SEE_OTHER);
    }













    #[Route('/reservations/contrat/{id}' , name: 'client_bundel', methods: ['GET'])]
    public function generatePdfBundel(Request $request, AReservation $aReservation,Pdf $snappy): Response
    {

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Document</title>
            </head>
            <body style="font-family: "Courier New", Courier, monospace; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <h1 style="text-align:center;">Recu de location</h1>
                <table style="width:100%;">
                    <tr style="width:100%;">
                        <td style="width: 40%; border: 0px;">
                            <div style="
                            display:flex;
                            background-color:rgb(255, 255, 255);
                            flex-direction: column;
                            ">
                                <h3 style="margin:0px; margin-top: 10px; margin-bottom: 10px;">Bailleur</h3>
                                <h4 style="margin:0px;">'.$aReservation->getRclient()->getCnom().' '.$aReservation->getRclient()->getCprenom().'</h4>
                                <h4 style="margin:0px;">Adresse</h4>
                                <h4 style="margin:0px;">Telephone</h4>
                            </div>
                        </td>
                        <td style="width: 30%;border: 0px;"></td>
                        <td style="width: auto;border: 0px;">
                            <div style="
                            display:flex;
                            background-color:rgb(255, 255, 255);
                            flex-direction: column;
                            ">
                                <h3 style="margin:0px; margin-top: 10px; margin-bottom: 10px;">Locataire</h3>
                                <h4 style="margin:0px;">'.$aReservation->getRannonce()->getAproprietaire()->getPnom().' '.$aReservation->getRannonce()->getAproprietaire()->getPprenom().'</h4>
                                <h4 style="margin:0px;">Adresse</h4>
                                <h4 style="margin:0px;">Telephone</h4>
                                <font style="font-weight: bold; margin-top: 30px; margin-bottom: 20px;">
                                    Fait a siteweb.com, Date
                                </font>
                            </div>
                        </td>
                    </tr>
                </table>

                <table style="border: 1px solid rgb(152, 152, 152); width: 100%; padding: 10px; margin-bottom: 20px;">
                    <tr style="margin-bottom: 10px;">
                        <td style="border: 0px;">
                            <font style="font-weight: bold; border-bottom: 2px solid rgb(49, 48, 48);">
                                Adresse de location
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 0px;">
                            Adresse
                        </td>
                    </tr>
                </table>

                <style>
                    tr{
                        display: flex;
                        justify-content: space-between;
                        margin:0px;
                    }
                    td, th{
                        border: 1px solid rgb(163, 163, 163);
                        width: 25%;
                        padding: 20px;
                        margin:0px;
                    }
                    .t{
                        background-color: rgb(207, 207, 207);
                        border: 0px;
                    }
                </style>
                <table style="width: 100%; vertical-align: center;">
                    <tr>
                        <th class="t">Type</th>
                        <th class="t">Date d\'entree</th>
                        <th class="t">Date de sortie</th>
                        <th class="t">Montant</th>
                    </tr>
                    <tr>
                        <td>...</td>
                        <td>...</td>
                        <td>...</td>
                        <td>...</td>
                    </tr>
                </table>

                <div style="width: 80%; display: flex; flex-direction: column; align-items: flex-start; margin-top: 10px;">
                    <font style="font-weight: 400;">
                        Date de paiement : Date
                    </font>
                    </br>
                    <font style="font-weight: 400;">
                        Methode de paiement : virement bancaire;
                    </font>
                </div>

                <div style="width: 80%; display: flex; flex-direction: column; align-items: flex-end; margin-top: 40px;">
                    <img src="" alt="" width="150px">
                </div>
                
            </body>
            </html>
        ';

        $filename = "my first pdf with snappy";
        //create pdf
        $websiteUrl = "http://ourcodeworld.com";
        return new Response(
            $snappy->getOutputFromHtml($html),
            // Status code ok
            200,
            array(
                'content-type' => 'application/pdf',
                'content-disposition' => 'inline; filename="'.$filename.'.pdf"',
            )
        );
        // $content = "<html>first pdf</html>";
        // $html = $this->render('client/a_reservation/pdf_generator/index.html.twig', [
        //     'content' => $content,
        // ]);

        // $pdf->showPdfFile($html);

        // $html="<html>hello world</html>";
        // $pdf->showPdfFile($html);

        // $pdf = new mPDF();

        // $pdf->WriteHTML($content);
        // $contrat = $pdf->Output('','S');
        // //echo $contrat;
        // return $this->render('client/a_reservation/pdf_generator/index.html.twig', [
        //     'content' => $content,
        // ]);
    }

}







