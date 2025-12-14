const fs = require('fs-extra');
const path = require('path');
const { execSync } = require('child_process');
const chokidar = require('chokidar');

const root = path.resolve(__dirname); // = /lite/admin
const vueCli = path.resolve(root, 'node_modules/.bin/vue-cli-service');

const distDev = path.join(root, 'dist-dev');
const distProd = path.join(root, 'dist-prod');
const distFinal = path.join(root, 'dist');

let isBuilding = false;
let queued = false;

async function build() {
  if (isBuilding) {
    queued = true;
    return;
  }

  isBuilding = true;
  try {
    console.log('\n🛠️ Building dev (expanded)...');
    execSync(`${vueCli} build --mode development --dest "${distDev}"`, {
      cwd: root,
      stdio: 'inherit'
    });

    console.log('🛠️ Building prod (minified)...');
    execSync(`${vueCli} build --mode production --dest "${distProd}"`, {
      cwd: root,
      stdio: 'inherit'
    });

    console.log('🧪 Running webpack-lite build...');
    execSync('yarn run build-webpack-lite', {
      cwd: root,
      stdio: 'inherit'
    });

    console.log('🔗 Merging into final dist/...');
    await fs.emptyDir(distFinal);
    for (const src of [distDev, distProd]) {
      if (await fs.pathExists(src)) {
        await fs.copy(src, distFinal, { overwrite: true });
        console.log(`✔️ Merged from: ${src}`);
      }
    }

    await fs.remove(distDev);
    await fs.remove(distProd);

    console.log('🎉 Final dist created at:', distFinal);
  } catch (err) {
    console.error('❌ Build failed:', err);
  } finally {
    isBuilding = false;
    if (queued) {
      queued = false;
      build(); // run again if a change occurred mid-build
    }
  }
}

function watchAndRebuild() {
  console.log('👀 Watching for changes in src/...');
  chokidar.watch(path.join(root, 'src'), { ignoreInitial: true })
    .on('all', (event, filePath) => {
      console.log(`🔄 ${event} detected in ${filePath}`);
      build();
    });
}

build();
watchAndRebuild();