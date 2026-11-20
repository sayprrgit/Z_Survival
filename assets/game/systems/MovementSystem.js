export class MovementSystem {
    update(player, direction, dt) {
        player.isMoving = direction.x !== 0 || direction.y !== 0;
        player.x += direction.x * player.moveSpeed * dt;
        player.y += direction.y * player.moveSpeed * dt;
        if (player.isMoving) player.facing = direction;
    }
}
