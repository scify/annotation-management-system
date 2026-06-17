<?php

declare(strict_types=1);

use App\Models\User;
use App\Queries\Manager\ConnectManagerToAnnotatorsQuery;
use Illuminate\Support\Facades\DB;

describe('ConnectManagerToAnnotatorsQuery', function (): void {
    it('creates an annotator-of-manager row per annotator via connect', function (): void {
        // Arrange
        $manager = User::factory()->create();
        $annotators = User::factory()->count(2)->create();

        // Act
        new ConnectManagerToAnnotatorsQuery()->connect($manager->id, $annotators->pluck('id')->all());

        // Assert
        $this->assertDatabaseCount('annotator_of_managers', 2);
        foreach ($annotators as $annotator) {
            $this->assertDatabaseHas('annotator_of_managers', [
                'manager_id' => $manager->id,
                'annotator_id' => $annotator->id,
            ]);
        }
    });

    it('bulk inserts annotator links with timestamps', function (): void {
        // Arrange
        $manager = User::factory()->create();
        $annotators = User::factory()->count(2)->create();

        // Act
        new ConnectManagerToAnnotatorsQuery()->bulkConnect($manager->id, $annotators->pluck('id')->all());

        // Assert
        $this->assertDatabaseCount('annotator_of_managers', 2);
        $link = DB::table('annotator_of_managers')->where('manager_id', $manager->id)->first();
        expect($link?->created_at)->not->toBeNull()
            ->and($link?->updated_at)->not->toBeNull();
    });

    it('is a no-op when bulk connecting an empty list', function (): void {
        // Arrange
        $manager = User::factory()->create();

        // Act
        new ConnectManagerToAnnotatorsQuery()->bulkConnect($manager->id, []);

        // Assert
        $this->assertDatabaseCount('annotator_of_managers', 0);
    });

    it('does not duplicate an already-linked annotator on bulk connect', function (): void {
        // Arrange
        $manager = User::factory()->create();
        $annotator = User::factory()->create();
        $query = new ConnectManagerToAnnotatorsQuery();
        $query->bulkConnect($manager->id, [$annotator->id]);

        // Act — link the same annotator again
        $query->bulkConnect($manager->id, [$annotator->id]);

        // Assert
        $this->assertDatabaseCount('annotator_of_managers', 1);
    });
});
