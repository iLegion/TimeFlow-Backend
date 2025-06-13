<mjml>
    <mj-head>
        <mj-font name="Roboto" href="https://fonts.googleapis.com/css?family=Roboto:400,700" />
        <mj-attributes>
            <mj-all padding="0" font-family="Roboto, Arial, sans-serif" />
            <mj-text font-size="16px" line-height="24px" color="#1D1D1D" />
            <mj-button background-color="#1D1D1D" color="#FFFFFF" border-radius="6px" font-size="18px" padding="16px 30px" />
            <mj-section background-color="#FFFFFF" />
            <mj-column padding="0" />
            <mj-wrapper padding="0" />
            <mj-class name="light-text" color="#777777" />
            <mj-class name="dark-text" color="#1D1D1D" />
            <mj-class name="header-section" background-color="#1D1D1D" padding="15px 0" />
            <mj-class name="main-container" box-shadow="0 4px 10px rgba(0, 0, 0, 0.05)" />
        </mj-attributes>
        <mj-style inline="inline">
            .header-section {
            border-top-left-radius: 8px !important;
            border-top-right-radius: 8px !important;
            border-bottom: 3px solid #EEEEEE;
            }

            .header-section > table {
            border-top-left-radius: 8px !important;
            border-top-right-radius: 8px !important;
            }

            .footer-section {
            border-bottom-left-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            border-top: 3px solid #EEEEEE;
            }

            .footer-section > table {
            border-bottom-left-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            }
        </mj-style>
    </mj-head>
    <mj-body background-color="#f4f4f4">
        <mj-wrapper mj-class="main-container" padding="30px 0">
            <mj-section mj-class="header-section" css-class="header-section" padding="15px 25px">
                <mj-column>
                    <mj-image src="{{ $message->embed($system['logo']) }}" alt="{{ $system['title'] }} Logo" width="70px" />
                </mj-column>
            </mj-section>

            <mj-section background-color="#FFFFFF" padding="45px 25px">
                <mj-column>
                    <mj-text mj-class="dark-text" align="center" font-size="28px" font-weight="bold" padding-bottom="20px">
                        {{ $system['title'] }}
                    </mj-text>

                    <mj-divider border-color="#eeeeee" border-width="1px" padding-top="0" padding-bottom="30px" />

                    <mj-text font-size="20px" font-weight="bold" padding-bottom="25px">
                        {{ __('mails.user.registered.content.hi', ['value' => $user->name]) }}
                    </mj-text>

                    <mj-text padding-bottom="25px">
                        {{ __('mails.user.registered.content.main', ['value' => $system['title']]) }}
                    </mj-text>
                </mj-column>
            </mj-section>

            <mj-section>
                <mj-column>
                    <mj-divider border-color="#eeeeee" border-width="1px" padding-top="0" />
                </mj-column>
            </mj-section>

            <mj-section css-class="footer-section" padding="30px 25px" background-color="#1D1D1D">
                <mj-column>
                    <mj-text color="#FFFFFF" font-size="15px">
                        {{ __('mails.main.Sincerely') }}<br />{{ __('mails.main.Team', ['value' => $system['title']]) }}
                    </mj-text>
                </mj-column>
            </mj-section>
        </mj-wrapper>
    </mj-body>
</mjml>