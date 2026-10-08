<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Report;
use App\Entity\ReportPhoto;
use App\Entity\User;
use App\Enum\ReportSource;
use App\Enum\ReportStatus;
use App\Form\ReportType;
use App\Repository\ReportRepository;
use App\Service\ReportScoringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ReportController extends AbstractController
{
    public function index(ReportRepository $reportRepository): Response
    {
        return $this->render('report/index.html.twig', [
            'reports' => $reportRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        ReportScoringService $reportScoringService,
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $report = new Report();
        $report->setAuthor($user);
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report
                ->setReference('SIG-'.strtoupper(substr((string) $report->getId(), 0, 8)))
                ->setStatus(ReportStatus::from((string) $form->get('status')->getData()))
                ->setSource(ReportSource::from((string) $form->get('source')->getData()))
                ->setImpactScore((int) $form->get('impactScore')->getData())
                ->setUrgencyScore((int) $form->get('urgencyScore')->getData())
                ->setAggravationScore((int) $form->get('aggravationScore')->getData());

            $reportScoringService->applyPriorityFromScore($report);

            $files = $form->get('photos')->getData();
            if (is_iterable($files)) {
                foreach ($files as $file) {
                    if (!$file instanceof UploadedFile) {
                        continue;
                    }

                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = (string) $slugger->slug($originalFilename);
                    $extension = $file->guessExtension() ?: 'bin';
                    $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid('', true), $extension);
                    $destination = $this->getParameter('kernel.project_dir').'/public/uploads/reports';
                    if (!is_dir($destination)) {
                        mkdir($destination, 0777, true);
                    }
                    $file->move($destination, $newFilename);

                    $photo = new ReportPhoto();
                    $photo->setReport($report)
                        ->setPath('/uploads/reports/'.$newFilename)
                        ->setOriginalFilename($originalFilename)
                        ->setMimeType($file->getMimeType() ?? 'application/octet-stream')
                        ->setSize((int) $file->getSize());
                    $report->addPhoto($photo);
                }
            }

            $entityManager->persist($report);
            $entityManager->flush();

            $this->addFlash('success', 'L’incident a bien été créé.');

            return $this->redirectToRoute('app_reports_index');
        }

        return $this->render('report/new.html.twig', [
            'form' => $form,
        ]);
    }
}
