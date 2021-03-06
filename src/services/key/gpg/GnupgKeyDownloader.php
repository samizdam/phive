<?php
namespace PharIo\Phive;

class GnupgKeyDownloader implements KeyDownloader {

    const PATH = '/pks/lookup';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Url[]
     */
    private $keyServers = [];

    /**
     * @var Cli\Output
     */
    private $output;

    /**
     * @param HttpClient $httpClient
     * @param Url[]      $keyServers
     * @param Cli\Output $output
     */
    public function __construct(HttpClient $httpClient, array $keyServers, Cli\Output $output) {
        $this->httpClient = $httpClient;
        $this->keyServers = $keyServers;
        $this->output = $output;
    }

    /**
     * @param string $keyId
     *
     * @return PublicKey
     * @throws DownloadFailedException
     */
    public function download($keyId) {
        $publicParams = [
            'search'  => '0x' . $keyId,
            'op'      => 'get',
            'options' => 'mr'
        ];
        $infoParams = array_merge($publicParams, [
            'op' => 'index'
        ]);
        foreach ($this->keyServers as $keyServer) {
            $this->output->writeInfo(sprintf('Trying %s', $keyServer));

            $keyInfo = $this->httpClient->get(new Url($keyServer . self::PATH), $infoParams);
            if ($keyInfo->getHttpCode() != 200) {
                $this->output->writeWarning(
                    sprintf('Failed with status code %s: %s', $keyInfo->getHttpCode(), $keyInfo->getErrorMessage())
                );
                continue;
            }

            $publicKey = $this->httpClient->get(new Url($keyServer . self::PATH), $publicParams);
            if ($publicKey->getHttpCode() != 200) {
                $this->output->writeWarning(
                    sprintf('Failed with status code %s: %s', $publicKey->getHttpCode(), $publicKey->getErrorMessage())
                );
                continue;
            }

            $this->output->writeInfo('Sucessfully downloaded key');
            return new PublicKey($keyId, $keyInfo->getBody(), $publicKey->getBody());
        }
        throw new DownloadFailedException(sprintf('PublicKey %s not found on key servers', $keyId));
    }

}
