<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include YBSU_DIR . '/utils/data.php';

$post   =   $_POST;
$gsql   =   new GSql();
?>

<div class="wrap">
    <h1>Sparq SEO Update</h1>

    <form method="POST">
        <table class="form-table">
            <tr>
                <th scope="row"><label>Enter Google Spreadsheet:</label></th>
                <td><input type="url" placeholder="Enter Google Spreadsheet URL" name="doc_url" class="regular-text" value="<?php echo ( isset($post['doc_url']) ) ? $post['doc_url']: ""; ?>"></td>
            </tr>

            <?php
            if ( isset($post['doc_url']) && $post['doc_url'] != "" ) {
                ?>
                 <tr>
                    <th scope="row"><label>Enter document auth:</label></th>
                    <td>
                        <input type="text" placeholder="Enter authentication key" name="auth_key" 
                            class="regular-text" value="<?php echo (isset( $post['auth_key'] ) ? $post['auth_key']: ( ($gsql->isTokenExists()) ? $gsql->getExistingToken()['refresh_token'] : $gsql->getClient() ) ); ?>">
                        <p><?php $gsql->getClient(); ?></p>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Continue">
        </p>
    </form>

    <?php
    if ( isset( $post['doc_url'] ) && $post['doc_url'] != "" && 
        isset( $post['auth_key'] ) && $post['auth_key'] != "" ) {
            $gsql->runPatch( $post['doc_url'], $post['auth_key'] );
    }
    ?>
</div>
