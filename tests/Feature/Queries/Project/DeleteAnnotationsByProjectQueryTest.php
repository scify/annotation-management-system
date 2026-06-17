<?php

declare(strict_types=1);

use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\Project;
use App\Models\SubProject;
use App\Queries\Project\DeleteAnnotationsByProjectQuery;

describe('DeleteAnnotationsByProjectQuery', function (): void {
    it('deletes annotations belonging to the project via its sub-projects and assignments', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create(['project_id' => $project->id]);
        $assignment = AnnotationAssignment::factory()->create(['sub_project_id' => $subProject->id]);
        $annotation = Annotation::factory()->create(['annotation_assignment_id' => $assignment->id]);

        // Act
        new DeleteAnnotationsByProjectQuery()->execute($project->id);

        // Assert
        $this->assertDatabaseMissing('annotations', ['id' => $annotation->id]);
    });

    it('does not delete annotations belonging to other projects', function (): void {
        // Arrange
        $project = Project::factory()->create();
        $otherSubProject = SubProject::factory()->create(['project_id' => Project::factory()]);
        $otherAssignment = AnnotationAssignment::factory()->create(['sub_project_id' => $otherSubProject->id]);
        $otherAnnotation = Annotation::factory()->create(['annotation_assignment_id' => $otherAssignment->id]);

        // Act
        new DeleteAnnotationsByProjectQuery()->execute($project->id);

        // Assert
        $this->assertDatabaseHas('annotations', ['id' => $otherAnnotation->id]);
    });
});
