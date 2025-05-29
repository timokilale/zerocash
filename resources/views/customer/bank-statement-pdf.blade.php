<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroCash Bank Statement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 18px;
            color: #666;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 150px;
            padding: 3px 0;
        }
        
        .info-value {
            display: table-cell;
            padding: 3px 0;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin: 20px 0 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-label {
            display: table-cell;
            font-weight: bold;
            width: 200px;
            padding: 5px 0;
        }
        
        .summary-value {
            display: table-cell;
            text-align: right;
            padding: 5px 0;
            font-weight: bold;
        }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
        }
        
        .transactions-table th {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }
        
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .transactions-table .text-left {
            text-align: left;
        }
        
        .transactions-table .text-right {
            text-align: right;
        }
        
        .debit {
            color: #dc3545;
        }
        
        .credit {
            color: #28a745;
        }
        
        .balance {
            font-weight: bold;
            color: #007bff;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #007bff;
        }
        
        .footer-balance {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin: 15px 0;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .accounts-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .account-row {
            display: table-row;
        }
        
        .account-cell {
            display: table-cell;
            padding: 5px 10px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        
        .account-cell.value {
            background-color: #fff;
            text-align: right;
            font-weight: bold;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 123, 255, 0.1);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="watermark">ZEROCASH</div>
    
    <!-- Header -->
    <div class="header">
        <h1>ZEROCASH BANKING SYSTEM</h1>
        <h2>OFFICIAL BANK STATEMENT</h2>
    </div>
    
    <!-- Customer Information -->
    <div class="info-section">
        <div class="section-title">CUSTOMER INFORMATION</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Customer Name:</div>
                <div class="info-value">{{ $user->first_name }} {{ $user->last_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Customer ID:</div>
                <div class="info-value">{{ $user->customer_id ?? $user->username }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statement Period:</div>
                <div class="info-value">
                    {{ $transactionsWithBalance->first() ? $transactionsWithBalance->first()->created_at->format('d M Y') : 'N/A' }}
                    to
                    {{ $transactionsWithBalance->last() ? $transactionsWithBalance->last()->created_at->format('d M Y') : 'N/A' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Statement Date:</div>
                <div class="info-value">{{ now()->format('d M Y H:i:s') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Account Information -->
    <div class="info-section">
        <div class="section-title">ACCOUNT INFORMATION</div>
        <div class="accounts-grid">
            @foreach($accounts as $account)
            <div class="account-row">
                <div class="account-cell">Account Number</div>
                <div class="account-cell value">{{ $account->account_number }}</div>
                <div class="account-cell">Type</div>
                <div class="account-cell value">{{ $account->accountType->name }}</div>
                <div class="account-cell">Balance</div>
                <div class="account-cell value">TSh {{ number_format($account->balance, 2) }}</div>
                <div class="account-cell">Status</div>
                <div class="account-cell value {{ $account->status === 'active' ? 'status-active' : 'status-inactive' }}">
                    {{ ucfirst($account->status) }}
                </div>
            </div>
            @endforeach
        </div>
        <div class="footer-balance">
            Total Available Balance: TSh {{ number_format($totalCurrentBalance, 2) }}
        </div>
    </div>
    
    <!-- Transaction Summary -->
    <div class="info-section">
        <div class="section-title">TRANSACTION SUMMARY</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-label">Total Credits:</div>
                <div class="summary-value credit">TSh {{ number_format($totalCredit, 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Debits:</div>
                <div class="summary-value debit">TSh {{ number_format($totalDebit, 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Fees Charged:</div>
                <div class="summary-value debit">TSh {{ number_format($totalFees, 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Net Change:</div>
                <div class="summary-value {{ ($totalCredit - $totalDebit) >= 0 ? 'credit' : 'debit' }}">
                    TSh {{ number_format($totalCredit - $totalDebit, 2) }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transactions -->
    <div class="section-title">TRANSACTION DETAILS</div>
    
    @if($transactionsWithBalance->count() > 0)
        <table class="transactions-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 12%;">Transaction #</th>
                    <th style="width: 20%;">Description</th>
                    <th style="width: 10%;">Reference</th>
                    <th style="width: 10%;">Debit (TSh)</th>
                    <th style="width: 10%;">Credit (TSh)</th>
                    <th style="width: 8%;">Fee (TSh)</th>
                    <th style="width: 12%;">Balance (TSh)</th>
                    <th style="width: 6%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactionsWithBalance as $transaction)
                    @php
                        $isCredit = in_array($transaction->receiver_account_id, $accountIds->toArray());
                        $isDebit = in_array($transaction->sender_account_id, $accountIds->toArray());
                        
                        $debitAmount = '';
                        $creditAmount = '';
                        
                        if ($isDebit) {
                            $debitAmount = number_format($transaction->amount + $transaction->fee_amount, 2);
                        } elseif ($isCredit) {
                            $creditAmount = number_format($transaction->amount, 2);
                        }
                        
                        $affectedAccount = $accounts->firstWhere('id', $transaction->account_id);
                    @endphp
                    <tr>
                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-left">{{ $transaction->transaction_number }}</td>
                        <td class="text-left">{{ $transaction->description }}</td>
                        <td class="text-left">{{ $transaction->reference ?? '-' }}</td>
                        <td class="text-right debit">{{ $debitAmount }}</td>
                        <td class="text-right credit">{{ $creditAmount }}</td>
                        <td class="text-right">{{ $isDebit ? number_format($transaction->fee_amount, 2) : '-' }}</td>
                        <td class="text-right balance">{{ number_format($transaction->running_balance, 2) }}</td>
                        <td>{{ ucfirst($transaction->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #666;">No transactions found for the selected period.</p>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <div class="section-title">CURRENT ACCOUNT BALANCES</div>
        @foreach($accounts as $account)
            <div class="summary-grid">
                <div class="summary-row">
                    <div class="summary-label">{{ $account->account_number }} ({{ $account->accountType->name }}):</div>
                    <div class="summary-value">TSh {{ number_format($account->balance, 2) }}</div>
                </div>
            </div>
        @endforeach
        
        <div class="footer-balance">
            TOTAL AVAILABLE BALANCE: TSh {{ number_format($totalCurrentBalance, 2) }}
        </div>
        
        <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #666;">
            <p>This is an official statement generated by ZeroCash Banking System</p>
            <p>Generated on {{ now()->format('d M Y \a\t H:i:s') }}</p>
            <p>For inquiries, please contact customer service</p>
        </div>
    </div>
</body>
</html>
