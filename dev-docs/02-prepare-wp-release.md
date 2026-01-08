## Prepare the `wp-release`
**For internal use only**

As we want to submit to WordPress.org plugin hub, we need to **includes all libraries and assets to the package**. We use the GIT branch `wp-release` to keep this package.

We need to include all vendors to the repo then remove all `require` things in the composer.json file for skipping dependencies when this package being required.
- Switch to `master` branch and pull all updated files from remote
- Switch to `wp-release` branch
- Delete all vendors
```
rm -rf vendor public-assets src resources
```

- Copy all needed files from master to this branch
```
git checkout master -- public-assets resources src .editorconfig composer.json composer.lock yivic-rest-api-bootstrap.php yivic-rest-api.php yivic-rest-api-init.php package* bun* *.js
```

- Install and add vendors
```
composer80 install --no-dev
```

- Prepare assets (should use node 20)
```
bun install
bun run build
```

- Remove unused stuffs
```
rm -rf docker-compose*
```

- Remove all packages in `composer.json` for not pulling them again when another project use this package on with `wp-release`
- Update readme.txt
- Re-add assets and vendors
```
git add --force public-assets/dist vendor
```

- Then add all files to the repo, commit and push
