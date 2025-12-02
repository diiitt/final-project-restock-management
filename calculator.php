<?php
session_start();
require 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Pintar - Restock Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .calculator {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .calc-display {
            font-size: 1.5rem;
            height: 60px;
            text-align: right;
            background: white;
            border: 2px solid #dee2e6;
        }
        .calc-btn {
            height: 50px;
            font-size: 1.1rem;
            border: none;
            margin: 2px;
        }
        .calc-btn-number {
            background: white;
        }
        .calc-btn-operator {
            background: #6c757d;
            color: white;
        }
        .calc-btn-equals {
            background: #28a745;
            color: white;
        }
        .calc-btn-clear {
            background: #dc3545;
            color: white;
        }
        .calc-btn-memory {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-boxes"></i> Restock Management
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Halo, <?= $_SESSION['nama'] ?>
                </span>
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-calculator"></i> Kalkulator Pintar
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="calculator">
                            <!-- Display -->
                            <input type="text" class="form-control calc-display mb-3" id="display" readonly value="0">
                            
                            <!-- Memory Functions -->
                            <div class="row mb-2">
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-memory w-100" onclick="memoryClear()">MC</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-memory w-100" onclick="memoryRecall()">MR</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-memory w-100" onclick="memoryAdd()">M+</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-memory w-100" onclick="memorySubtract()">M-</button>
                                </div>
                            </div>
                            
                            <!-- First Row -->
                            <div class="row mb-2">
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-clear w-100" onclick="clearDisplay()">C</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-clear w-100" onclick="clearEntry()">CE</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="backspace()">⌫</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="appendOperator('/')">/</button>
                                </div>
                            </div>
                            
                            <!-- Second Row -->
                            <div class="row mb-2">
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('7')">7</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('8')">8</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('9')">9</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="appendOperator('*')">×</button>
                                </div>
                            </div>
                            
                            <!-- Third Row -->
                            <div class="row mb-2">
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('4')">4</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('5')">5</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('6')">6</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="appendOperator('-')">-</button>
                                </div>
                            </div>
                            
                            <!-- Fourth Row -->
                            <div class="row mb-2">
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('1')">1</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('2')">2</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('3')">3</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="appendOperator('+')">+</button>
                                </div>
                            </div>
                            
                            <!-- Fifth Row -->
                            <div class="row">
                                <div class="col-6">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendNumber('0')">0</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-number w-100" onclick="appendDecimal()">.</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-equals w-100" onclick="calculate()">=</button>
                                </div>
                            </div>
                            
                            <!-- Additional Functions -->
                            <div class="row mt-3">
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="square()">x²</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="squareRoot()">√</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="percentage()">%</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn calc-btn calc-btn-operator w-100" onclick="inverse()">1/x</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Calculation History -->
                        <div class="mt-4">
                            <h6>Riwayat Perhitungan:</h6>
                            <div id="history" class="border p-2" style="height: 100px; overflow-y: auto; background: white;">
                                <!-- History will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let display = document.getElementById('display');
        let currentInput = '0';
        let previousInput = '';
        let operator = null;
        let shouldResetDisplay = false;
        let memory = 0;
        let history = [];

        function updateDisplay() {
            display.value = currentInput;
        }

        function appendNumber(number) {
            if (shouldResetDisplay) {
                currentInput = '';
                shouldResetDisplay = false;
            }
            
            if (currentInput === '0') {
                currentInput = number;
            } else {
                currentInput += number;
            }
            updateDisplay();
        }

        function appendOperator(op) {
            if (operator !== null) calculate();
            previousInput = currentInput;
            operator = op;
            shouldResetDisplay = true;
        }

        function appendDecimal() {
            if (shouldResetDisplay) {
                currentInput = '0';
                shouldResetDisplay = false;
            }
            if (!currentInput.includes('.')) {
                currentInput += '.';
            }
            updateDisplay();
        }

        function calculate() {
            if (operator === null || shouldResetDisplay) return;
            
            let prev = parseFloat(previousInput);
            let current = parseFloat(currentInput);
            let result;
            
            switch (operator) {
                case '+':
                    result = prev + current;
                    break;
                case '-':
                    result = prev - current;
                    break;
                case '*':
                    result = prev * current;
                    break;
                case '/':
                    if (current === 0) {
                        result = 'Error: Div by 0';
                    } else {
                        result = prev / current;
                    }
                    break;
                default:
                    return;
            }
            
            // Add to history
            const calculation = `${previousInput} ${operator} ${currentInput} = ${result}`;
            history.unshift(calculation);
            if (history.length > 5) history.pop();
            updateHistory();
            
            if (typeof result === 'number') {
                currentInput = result.toString();
            } else {
                currentInput = result;
            }
            
            operator = null;
            previousInput = '';
            shouldResetDisplay = true;
            updateDisplay();
        }

        function clearDisplay() {
            currentInput = '0';
            previousInput = '';
            operator = null;
            shouldResetDisplay = false;
            updateDisplay();
        }

        function clearEntry() {
            currentInput = '0';
            updateDisplay();
        }

        function backspace() {
            if (currentInput.length > 1) {
                currentInput = currentInput.slice(0, -1);
            } else {
                currentInput = '0';
            }
            updateDisplay();
        }

        function square() {
            const num = parseFloat(currentInput);
            const result = num * num;
            addToHistory(`${num}² = ${result}`);
            currentInput = result.toString();
            shouldResetDisplay = true;
            updateDisplay();
        }

        function squareRoot() {
            const num = parseFloat(currentInput);
            if (num < 0) {
                currentInput = 'Error: Neg number';
            } else {
                const result = Math.sqrt(num);
                addToHistory(`√${num} = ${result}`);
                currentInput = result.toString();
            }
            shouldResetDisplay = true;
            updateDisplay();
        }

        function percentage() {
            const num = parseFloat(currentInput);
            const result = num / 100;
            addToHistory(`${num}% = ${result}`);
            currentInput = result.toString();
            shouldResetDisplay = true;
            updateDisplay();
        }

        function inverse() {
            const num = parseFloat(currentInput);
            if (num === 0) {
                currentInput = 'Error: Div by 0';
            } else {
                const result = 1 / num;
                addToHistory(`1/${num} = ${result}`);
                currentInput = result.toString();
            }
            shouldResetDisplay = true;
            updateDisplay();
        }

        // Memory Functions
        function memoryClear() {
            memory = 0;
        }

        function memoryRecall() {
            currentInput = memory.toString();
            updateDisplay();
        }

        function memoryAdd() {
            memory += parseFloat(currentInput);
        }

        function memorySubtract() {
            memory -= parseFloat(currentInput);
        }

        // History Functions
        function addToHistory(calculation) {
            history.unshift(calculation);
            if (history.length > 5) history.pop();
            updateHistory();
        }

        function updateHistory() {
            const historyElement = document.getElementById('history');
            historyElement.innerHTML = history.map(item => 
                `<div class="small text-muted">${item}</div>`
            ).join('');
        }

        // Keyboard support
        document.addEventListener('keydown', function(event) {
            const key = event.key;
            
            if (key >= '0' && key <= '9') {
                appendNumber(key);
            } else if (key === '.') {
                appendDecimal();
            } else if (key === '+' || key === '-' || key === '*' || key === '/') {
                appendOperator(key);
            } else if (key === 'Enter' || key === '=') {
                calculate();
            } else if (key === 'Escape' || key === 'Delete') {
                clearDisplay();
            } else if (key === 'Backspace') {
                backspace();
            }
        });

        // Initialize
        updateDisplay();
        updateHistory();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>