<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectDetailResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    #[OA\Get(path: '/api/projects', summary: 'Get active projects', tags: ['Projects'])]
    #[OA\Response(response: 200, description: 'Get active projects')]
    public function index()
    {
        return ProjectResource::collection(Project::active()->get());
    }

    #[OA\Get(path: '/api/project/{project}', summary: 'Get a project', tags: ['Projects'])]
    #[OA\Parameter(
        name: 'project',
        in: 'path',
        required: true,
        description: 'Slug of project to return',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(response: 200, description: 'Get a project')]
    public function show(Project $project)
    {
        return ProjectDetailResource::make($project);
    }
}
