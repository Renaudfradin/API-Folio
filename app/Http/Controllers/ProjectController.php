<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectDetailResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use OpenApi\Annotations as OA;

class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/projects",
     *     summary="Get all projects",
     *     tags={"Projects"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get all projects"
     *     )
     * )
     */
    public function index()
    {
        return ProjectResource::collection(Project::all());
    }

    /**
     * @OA\Get(
     *     path="/api/project/{project}",
     *     summary="Get a project",
     *     tags={"Projects"},
     *
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         description="ID of project to return",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get a project"
     *     )
     * )
     */
    public function show(Project $project)
    {
        return ProjectDetailResource::make($project);
    }
}
