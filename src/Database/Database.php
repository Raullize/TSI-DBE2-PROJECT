<?php

namespace Database;

use PDO;
use PDOException;
use Error\ApiException;

class Database
{
	private static string $database = __DIR__ . '/database.sqlite';

	// Instância única da conexão (Singleton)
	private static ?PDO $connection = null;

	// Método estático para obter a conexão
	public static function getConnection(): PDO
	{
		// se ainda não existe uma conexão, cria uma
		if (self::$connection === null) {
			try {
				// Cria a conexão uma única vez
				$dsn = "sqlite:" . self::$database;
				self::$connection = new PDO($dsn);

				// Configurações da conexão para gerar exceções e retornar arrays associativos
				self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

				// Ativa as chaves estrangeiras no SQLite
				self::$connection->exec("PRAGMA foreign_keys = ON;");
			} catch (PDOException $e) {
				throw new APIException("Erro ao conectar ao banco de dados: " . $e->getMessage(), 500);	
			}
		}

		// Retorna sempre a mesma instância
		return self::$connection;
	}
}