export class MovementSystem {
    update(player, direction, dt, world) {
        player.isMoving = direction.x !== 0 || direction.y !== 0;
        player.x += direction.x * player.moveSpeed * dt;
        player.y += direction.y * player.moveSpeed * dt;
        world?.resolveCircle(player);
        if (player.isMoving) player.facing = direction;
    }
}
