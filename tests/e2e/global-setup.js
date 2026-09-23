import { execSync } from 'node:child_process'

export default async function globalSetup() {
  execSync('php artisan e2e:seed', { stdio: 'inherit' })
  execSync('php artisan e2e:reset-settings', { stdio: 'inherit' })
}
