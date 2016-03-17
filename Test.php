<?php
/**
 * Created by PhpStorm.
 * User: SZL
 * Date: 2016/3/17 0017
 * Time: 23:34
 */
require "lib/avro.php";

$avro         = json_decode(file_get_contents('avro-protocol.json'), true);
$host = "127.0.0.1";
$avroProtocol = new AvroProtocol();
$protocol     = $avroProtocol->real_parse($avro);
$schema       = AvroSchema::real_parse($avro['types'][0]);

$datum_writer = new AvroIODatumWriter($schema);
$write_io     = new AvroStringIO();
$encoder      = new AvroIOBinaryEncoder($write_io);

$message = array('url' => 'http://dmyz.org', 'charset' => 'utf-8');
$datum_writer->write($message, $encoder);

$content      = $write_io->string();

$headers = array(
    "POST / HTTP/1.1",
    "Host:". $host,
    "Content-Type: avro/binary",
    "Content-Length: " . strlen($content),
);

$socket = stream_socket_client('localhost:3000', $errno, $errstr, 5);

if (!$socket)
    throw new Exception($errstr, $errno);

fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n");
fwrite($socket, $content);

$result = '';
while (!feof($socket)) {
    $result .= fgets($socket);
}
fclose($socket);

echo $result;