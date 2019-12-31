<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Info;
use App\Repository\InfoRepository;
class InfoController extends AbstractController
{
    /**
     * @Route("/", name="info")
     */
    public function index(Request $request)
    {
        return $this->render('info/index.html.twig');
    }
    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request, InfoRepository $infoRepository){
        $token = $request->request->get('token');
        $searchTerm = $request->request->get('search');
        $result = null;
        if ($this->isCsrfTokenValid('search-data', $token) && $request->isMethod('post')) {
            $result = $infoRepository->search($searchTerm);
        }
        return $this->render('info/index.html.twig', ['result' => $result]);
    }

    /**
     * @Route("/entry", name="entry")
     */
    public function entry(Request $request, MailerInterface $mailer, EntityManagerInterface $em)
    {
        $info = new Info();
        $token = $request->request->get('token');
        if ($this->isCsrfTokenValid('save-data', $token) && $request->isMethod('post')) {
            $date = $request->request->get('date');
            $subject = $request->request->get('subject');
            $description = $request->request->get('description');
            try {
                $email = (new Email())
                    ->from($this->getParameter('app.sender_email'))
                    ->to($this->getParameter('app.recipient_email'))
                    ->subject($subject)
                    ->text($description);
                $sentEmail = $mailer->send($email);
                $info->setDate($date);
                $info->setSubject($subject);
                $info->setDescription($description);
                $em->persist($info);
                $em->flush();
                return $this->redirect('/', 308);
            } catch (\Throwable $th) {
                
            }
        }
        return $this->render('info/entry.html.twig');
    }
}
