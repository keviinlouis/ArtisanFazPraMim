<?php

namespace DumpNamespace;


trait AttributesMasks
{
    /**
     * @param string $cpf
     * @return string
     */
    public function removeMaskCpf(string $cpf): string
    {
        return $this->removeMask($cpf);
    }

    /**
     * @param string $cep
     * @return string
     */
    public function removeMaskCep(string $cep): string
    {
        return $this->removeMask($cep);
    }

    /**
     * @param string $cnpj
     * @return string
     */
    public function removeMaskCnpj(string $cnpj): string
    {
        return $this->removeMask($cnpj);
    }

    /**
     * @param string $telefone
     * @return string
     */
    public function removeMaskTelefone(string $telefone): string
    {
        return $this->removeMask($telefone);
    }

    /**
     * @param string $string
     * @param array $itens
     * @return string
     */
    public function removeMask(string $string, array $itens = ['-', '.', '%', '$', ',', '/', '(', ')', ' ']): string
    {
        $string = str_replace($itens, '', $string);

        return $string;
    }

    /**
     * @param string $cpf
     * @return string
     */
    public function makeMaskCpf(string $cpf): string
    {
        return $this->makeMask($cpf, '###.###.###-##');
    }

    /**
     * @param string $cep
     * @return string
     */
    public function makeMaskZipCode(string $cep): string
    {
        return $this->makeMask($cep, '#####-###');
    }

    /**
     * @param string $cnpj
     * @return string
     */
    public function makeMaskCnpj(string $cnpj): string
    {
        return $this->makeMask($cnpj, '##.###.###/####-##');
    }

    /**
     * @param string $telefone
     * @return string
     */
    public function makeMaskTelefoneFixo(string $telefone): string
    {
        return $this->makeMask($telefone, '(##) ####-####');
    }

    /**
     * @param string $celular
     * @return string
     */
    public function makeMaskCelular(string $celular): string
    {
        return $this->makeMask($celular, '(##) ####-#####');
    }

    /**
     * @param string $dinheiro
     * @return string
     */
    public function makeMaskDinheiro(string $dinheiro): string
    {

        return 'R$ ' . number_format($dinheiro, 2, ',', '.');
    }

    /**
     * @param string $value
     * @param string $mask
     * @return string
     */
    public function makeMask(string $value, string $mask): string
    {
        $value = str_replace(" ", "", $value);

        for ($i = 0; $i < strlen($value); $i++) {
            $mask[strpos($mask, "#")] = $value[$i];
        }


        return $mask;
    }
}
