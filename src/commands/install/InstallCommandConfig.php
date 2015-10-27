<?php
namespace PharIo\Phive {

    use TheSeer\CLI;

    class InstallCommandConfig {

        /**
         * @var CLI\CommandOptions
         */
        private $cliOptions;

        /**
         * @var Config
         */
        private $config;

        /**
         * InstallCommandConfig constructor.
         *
         * @param CLI\CommandOptions $options
         * @param Config            $config
         */
        public function __construct(CLI\CommandOptions $options, Config $config) {
            $this->cliOptions = $options;
            $this->config = $config;
        }

        /**
         * @return Directory
         */
        public function getWorkingDirectory() {
            return $this->config->getWorkingDirectory();
        }

        /**
         * @return array
         * @throws CLI\CommandOptionsException
         */
        public function getRequestedPhars()
        {
            $phars = [];
            for ($i = 0; $i < $this->cliOptions->getArgumentCount(); $i++) {
                $argument = $this->cliOptions->getArgument($i);
                if (strpos($argument, 'https://') !== false) {
                    $phars[] = new Url($argument);
                } else {
                    $aliasSegments = explode('@', $argument, 2);
                    $parser = new VersionConstraintParser();
                    if (count($aliasSegments) === 2) {
                        $versionConstraint = $parser->parse($aliasSegments[1]);
                    } else {
                        $versionConstraint = new AnyVersionConstraint();
                    }
                    $phars[] = new PharAlias($aliasSegments[0], $versionConstraint);
                }
            }
            return $phars;
        }

        /**
         * @return bool
         */
        public function makeCopy() {
            return $this->cliOptions->isSwitch('copy');
        }

    }

}
