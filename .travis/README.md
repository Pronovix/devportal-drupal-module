## Running test locally

1. Set the required environment variables. (For example, with the `export` command.)

#### Required environment variables
* `DRUPAL_MODULE_NAME=devportal`
* `TEST_ROOT="RELATIVE_PATH_TO_MODULE_TEST_DIR"`, ex.:
  `modules/contrib/${DRUPAL_MODULE_NAME}/modules/api_reference/tests`

#### Optional environment variables**
* `DEPENDENCIES="--prefer-lowest"` # Install lowest versions from dependencies (default: highest)
* `DRUPAL_CORE=8.7.x-dev` # Install specific branch or tag from Drupal core. (Specified branch or tag must be available
  in the `webflo/drupal-core-require-dev` package as well.)

2. Remove all remnant containers, volumes and images from the previous build `docker-compose down --remove-orphans -v`

3. Build a new container with the current codebase - this command copies (not mounts!) all module files to the
   container: `docker-compose up --build`

4. Run tests in the container
```sh
docker-compose run php /opt/drupal-module/.travis/run-test.sh # to run all tests from TEST_ROOT
docker-compose run php /opt/drupal-module/.travis/run-test.sh modules/contrib/${DRUPAL_MODULE_NAME}/modules/api_reference/tests/src/FunctionalJavascript/OpenApiUploadTest.php # to run one specific test
```

**IMPORTANT: If you make changes on the tested code you have to repeat this process starting with step 1 (if you would
like change an environment values as well) or with step 2 otherwise your changes won't be applied in the next test
run.**

### Tips

If you would like to check the output of a failed test build you can download test logs from the container with this
command: `docker cp $(docker ps -f "name=devportal_module_tests_php" --format "{{.ID}}"):/mnt/files/log .`
