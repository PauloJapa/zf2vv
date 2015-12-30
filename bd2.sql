-- --------------------------------------------------------

--
-- Estrutura da tabela `taxaAjuste`
--

CREATE TABLE IF NOT EXISTS `taxaAjuste` (
`id` int(11) NOT NULL,
  `classe_id` int(11) DEFAULT NULL,
  `administradoras_id` int(11) DEFAULT NULL,
  `seguradora_id` int(11) DEFAULT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime DEFAULT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `validade` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ocupacao` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `cont_ele` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `conteudo` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `eletrico` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `sem_cont_ele` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `com_eletrico` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `sem_eletrico` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `unica` decimal(10,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

--
-- Extraindo dados da tabela `taxaAjuste`
--

INSERT INTO `taxaAjuste` (`id`, `classe_id`, `administradoras_id`, `seguradora_id`, `inicio`, `fim`, `status`, `validade`, `ocupacao`, `cont_ele`, `conteudo`, `eletrico`, `sem_cont_ele`, `com_eletrico`, `sem_eletrico`, `unica`) VALUES
(1 , 1   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  1.0000, 13.0000, 21.0000, -15.0000,   0.0000, 0.0000, 0.0000),
(2 , 2   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  2.0000, 12.0000, 22.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(3 , 3   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  3.0000, 11.0000, 23.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(4 , 4   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  4.0000, 10.0000, 24.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(5 , 5   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  5.0000,  9.0000, 25.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(6 , 6   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  6.0000,  8.0000, 26.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(7 , 7   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  7.0000,  7.0000, 27.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(8 , 8   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  8.0000,  6.0000, 28.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(9 , 9   , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01',  9.0000,  5.0000, 29.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(10, 15  , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01', 10.0000,  4.0000, 30.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(11, 16  , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01', 11.0000,  3.0000, 31.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(12, 17  , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01', 12.0000,  2.0000, 32.0000,   0.0000,   0.0000, 0.0000, 0.0000),
(14, 18  , 1, 2, '2015-12-21 00:00:00', '1000-01-01 00:00:00', 'A', 'anual' , '01', 13.0000,  1.0000, 33.0000, -22.0000,   0.0000, 0.0000, 0.0000),
(15, NULL, 1, 2, '2015-12-01 00:00:00', '1000-01-01 00:00:00', 'A', 'mensal', '04',  0.0000,  0.0000,  0.0000,   0.0000, -50.0000, 0.0000, 0.0000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `taxaAjuste`
--
ALTER TABLE `taxaAjuste`
 ADD PRIMARY KEY (`id`), ADD KEY `IDX_6E1F0CA08F5EA509` (`classe_id`), ADD KEY `IDX_6E1F0CA02AEE976D` (`administradoras_id`), ADD KEY `IDX_6E1F0CA0CF32153` (`seguradora_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `taxaAjuste`
--
ALTER TABLE `taxaAjuste`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `taxaAjuste`
--
ALTER TABLE `taxaAjuste`
ADD CONSTRAINT `FK_6E1F0CA02AEE976D` FOREIGN KEY (`administradoras_id`) REFERENCES `administradoras` (`id`),
ADD CONSTRAINT `FK_6E1F0CA08F5EA509` FOREIGN KEY (`classe_id`) REFERENCES `classe` (`id`),
ADD CONSTRAINT `FK_6E1F0CA0CF32153` FOREIGN KEY (`seguradora_id`) REFERENCES `seguradora` (`id`);


ALTER TABLE orcamento ADD taxa_ajuste NUMERIC(20, 8) NOT NULL;
ALTER TABLE fechados ADD taxa_ajuste NUMERIC(20, 8) NOT NULL;


